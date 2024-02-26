<?php

namespace App\Services;

use App\Models\TranslationModel;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DockerCommandService
{
    public function translateCommand(string $text)
    {
        // Отримання активної моделі перекладу (припустимо, що цей код вже написаний)
        $activeModel = TranslationModel::where('is_active', true)->firstOrFail();
        $modelPath = $activeModel->path; // Шлях до активної моделі
        $modelPath = storage_path("app/" . $modelPath);

        // Визначення шляху до Python скрипта
        $scriptPath = base_path('scripts/translate.py');

        // Створення процесу і передача шляху до моделі та тексту для перекладу
        $process = new Process(['python3', $scriptPath, $modelPath, $text]);
        $process->run();

        // Перевірка на помилки
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Повернення результату перекладу
        return trim($process->getOutput());
    }
}
