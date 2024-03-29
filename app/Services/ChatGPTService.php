<?php

namespace App\Services;


use OpenAI\Laravel\Facades\OpenAI;

class ChatGPTService
{
//    private const CORRECTION_CONTEXT_MESSAGE = <<<'EOD'
//Ти є інтерфейсом для виправлення невірно перекладених команд з природньої мови на команди bash та Docker CLI. Твоя задача - аналізувати команди, надані українською мовою, і перекладати їх згідно з контекстом bash/docker-cli, забезпечуючи їх коректність та можливість виконання на хост-машині. Кожна команда має бути сформульована для виконання в неінтерактивному режимі та гарантувати, що контейнер залишається в запущеному стані після завершення всіх дій. При формуванні команд важливо уникати використання символів, які можуть бути неправильно інтерпретовані оболонкою контейнера, наприклад, уникнення безпосереднього використання `;` як роздільника команд. Натомість, команди, які потребують виконання в контейнері, мають бути об'єднані в одну команду за допомогою '&&' або іншого відповідного синтаксису, який гарантує їх послідовне виконання без помилок. Це допоможе уникнути помилок виконання, пов'язаних з неправильним форматуванням команд. Надана команда буде включати оригінальний рядок на природній мові, невірно перекладену команду та повідомлення про помилку, що допоможе ідентифікувати причину некоректності перекладу.
//Приклад гарно перекладеного запиту для контейнера на базі Debian/Ubuntu:
//{
//  "input": "Створи новий контейнер з образу NGINX і встанови в нього PHP",
//  "output": "docker run -dit --name php-nginx-container nginx:latest && docker exec php-nginx-container bash -c 'apt-get update && apt-get install -y php-fpm php-mysql'"
//}
//Ти не повинен повертати нічого крім перекладеної команди чи повідомлення про помилку у вказаному форматі але ні в якому разі і помилку і перекладену команду разом.
//EOD;

    private const CORRECTION_CONTEXT_MESSAGE = <<<'EOD'
Ти є інтерфейсом для виправлення невірно перекладених команд з природньої мови на команди bash та Docker CLI. Твоя задача - аналізувати команди, надані українською мовою, і перекладати їх згідно з контекстом bash/docker-cli, забезпечуючи їх коректність та можливість виконання на хост-машині. Важливо точно визначати назви пакетів та їх версії, особливо при роботі з дистрибутивами Linux, де пакети можуть мати різні назви чи бути замінені іншими версіями. Наприклад, для встановлення Python використовуйте 'python3' замість 'python', оскільки більшість сучасних дистрибутивів Linux використовують Python 3 як стандарт. Подібно, замість 'php' або 'ruby', вказуйте конкретну версію пакету, якщо це необхідно. Після аналізу наданої команди та її контексту, повертай лише виправлену команду, придатну для виконання, без додавання будь-яких обгорток чи форматів. Кожна команда має бути сформульована для виконання в неінтерактивному режимі та гарантувати, що контейнер залишається в запущеному стані після завершення всіх дій. Перекладена команда має бути представлена чітко та лаконічно, включаючи всі необхідні дії для виконання задачі, вказаної в оригінальному запиті, з особливою увагою до точності назв пакетів та їх версій.
EOD;

    private const VALIDATION_CONTEXT_MESSAGE = <<<'EOD'
Ти є інтерфейсом для оцінки якості перекладу команд з природньої мови на команди bash та Docker CLI.
Твоя задача - аналізувати, наскільки точно оригінальна команда природною мовою перекладена на команду bash/docker-cli.
Перкладена команда має бути в неінтерактивному режимі та враховувати інші потенційні проблеми що можуть перешкоджати її запуску, так наприклад якщо комманда має виконати дії в контейнері після його запуску, ми маємо бути впевнені що комманда запущена.
Input має містити тільки валідну bash/docker cli команду без будь якої додаткової та зайвої інформації
Надайте оцінку від 1 до 10, де 10 означає ідеальний переклад, а 1 - повну відсутність відповідності.
Ти не повинен повертати нічого крім оцінки у форматі що відповідає регулярному виразу "/Оцінка: (\d+)/"
EOD;

    private const GENERATE_CONTEXT_MESSAGE = <<<'EOD'
Створи 10 команд українською мовою для інтерфейсу, що призначений для генерації команд, які можна буде перекласти на bash та Docker CLI. Кожна команда повинна бути унікальною та мати певну складність, відмінну від простих і базових команд. Команди мають бути сумісні з можливостями bash та Docker API. Зверни увагу на наступні деталі:

1. Використовуй лише українську мову для формулювання команд.
2. Зосередься на контексті Docker, враховуючи різні аспекти роботи з контейнерами та образами.
3. Уникай додавання будь-яких пояснень чи додаткової інформації — потрібні тільки команди.
4. Вивід має бути у форматі JSON, де кожна команда є окремим елементом масиву.
5. Уникай операції з застосування Dockerfile та docker-compose
6. Конкретизуй команди. Приклад погано конкретизованої комманди - Видалити певний локальний образ Docker. Приклад добре конкретизованої команди -  Видалити локальний образ Docker з ім'ям тест.

Приклад оформлення виводу:
[
    "Команда 1",
    "Команда 2",
    ...
]

Згенеруй команди, дотримуючись вищевказаних вимог та умов.

EOD;

    public function generateCommand(string $input)
    {
        $prompt = self::GENERATE_CONTEXT_MESSAGE."\n\n Приклад:{$input}";

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system', 'content' => 'Natural language to bash/dockerAPI translation service'
                ],
                [
                    'role' => 'user', 'content' => $prompt,
                ],
            ],
        ]);

        return $response->choices[0]->message->content ?? 'error: Не вдалося згенерувати команди';
    }

    public function correctCommand(string $originalCommand, string $incorrectCommand, string $error)
    {
        $prompt = self::CORRECTION_CONTEXT_MESSAGE . "\n\n### Оригінальна команда природньою мовою:\n" . $originalCommand . "\n### Невірно перекладена команда:\n" . $incorrectCommand . "\n### Повідомлення про помилку:\n" . $error . "\n\n### Завдання:\nВраховуючи надану інформацію, виправте невірно перекладену команду так, щоб вона була коректною для виконання на хост-машині в контексті bash/docker-cli. Забезпечте, щоб команда була сумісна з неінтерактивним режимом і враховувала поточний статус Docker контейнерів, якщо це необхідно.";


        $response = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system', 'content' => 'Natural language to bash/dockerAPI translation service'
                ],
                [
                    'role' => 'user', 'content' => $prompt,
                ],
            ],
        ]);

        return $response->choices[0]->message->content ?? 'error: Не вдалося отримати виправлення';
    }

    public function validateCommand(string $originalCommand, string $translatedCommand)
    {
        $prompt = self::VALIDATION_CONTEXT_MESSAGE . "\n\n### Оригінальна команда природною мовою:\n" . $originalCommand . "\n### Перекладена команда:\n" . $translatedCommand;

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system', 'content' => self::VALIDATION_CONTEXT_MESSAGE
                ],
                [
                    'role' => 'user', 'content' => $prompt,
                ],
            ],
        ]);

        $validationResult = $response->choices[0]->message->content ?? 'error: Не вдалося отримати оцінку';

        // Інтерпретація відповіді ChatGPT
        // Припустимо, що ChatGPT повертає оцінку у форматі "Оцінка: X", де X - число від 1 до 10
        if (preg_match('/Оцінка: (\d+)/', $validationResult, $matches)) {
            $score = (int)$matches[1];
            var_dump($score);
            if ($score >= 8) {
                // Якщо оцінка вище або дорівнює 9, вважаємо команду валідною
                return true;
            }
        }

        // Якщо оцінка нижче 7 або не вдалося отримати оцінку, вважаємо команду невалідною
        return false;
    }
}
