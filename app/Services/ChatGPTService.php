<?php

namespace App\Services;


use OpenAI\Laravel\Facades\OpenAI;

class ChatGPTService
{
    private const CORRECTION_CONTEXT_MESSAGE = <<<'EOD'
Ти є інтерфейсом для виправлення невірно перекладених команд з природньої мови на команди bash та Docker CLI. Твоя задача - аналізувати команди, надані українською мовою, і перекладати їх згідно з контекстом bash/docker-cli, забезпечуючи їх коректність та можливість виконання на хост-машині. Кожна команда має бути сформульована для виконання в неінтерактивному режимі та враховувати статус запущених контейнерів Docker, якщо це необхідно. Всі створені контейнери мають бути запущеними якщо не вказано інакше. Надана команда буде включати оригінальний рядок на природній мові, невірно перекладену команду та повідомлення про помилку, що допоможе ідентифікувати причину некоректності перекладу. Якщо в запиті вказано що необхідно встановити якісь пакети чи залежності це має бути зроблено. Якщо команда не відповідає контексту Docker, буде надано відповідь "error:{message}".
Приклад гарно перекладеного запиту:
{
  "input": "Створи новий контейнер з образу ALPINE LINUX і встанови в нього пхп 8.2",
  "output": "docker run -dit --name php-container alpine:latest && docker exec php-container sh -c \"apk update && apk add php82 php82-fpm php82-opcache php82-common php82-mbstring\"\n"
}
EOD;

    public function correctCommand(string $originalCommand, string $incorrectCommand, string $error)
    {
        $prompt = self::CORRECTION_CONTEXT_MESSAGE . "\n\n### Оригінальна команда природньою мовою:\n" . $originalCommand . "\n### Невірно перекладена команда:\n" . $incorrectCommand . "\n### Повідомлення про помилку:\n" . $error . "\n\n### Завдання:\nВраховуючи надану інформацію, виправте невірно перекладену команду так, щоб вона була коректною для виконання на хост-машині в контексті bash/docker-cli. Забезпечте, щоб команда була сумісна з неінтерактивним режимом і враховувала поточний статус Docker контейнерів, якщо це необхідно.";


        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
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
}
