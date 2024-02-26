<?php

namespace App\Services;

use App\Models\TranslationModel;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DockerCommandService
{
    public const WORKING_DIRECTORY = 'app/data';

    public function translateCommand(string $text): string
    {
        // Отримання активної моделі перекладу
        $activeModel = TranslationModel::where('is_active', true)->firstOrFail();
        $modelPath = $activeModel->path; // Шлях до активної моделі
        $modelPath = storage_path("app/" . $modelPath);

        // Визначення шляху до Python скрипта
        $scriptPath = base_path('scripts/translate.py');

        // Створення процесу і передача шляху до моделі та тексту для перекладу
        $process = new Process(['python3', $scriptPath, $modelPath, $text]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }

    public function executeCommand(string $command): array
    {
        $process = new Process(explode(' ', $command));
        $process->setWorkingDirectory(storage_path(self::WORKING_DIRECTORY));
        $process->run();

        return [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput(),
            'error' => $process->isSuccessful() ? null : $process->getErrorOutput(),
        ];
    }
}
