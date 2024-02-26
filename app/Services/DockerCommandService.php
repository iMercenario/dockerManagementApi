<?php

namespace App\Services;

use App\Models\TranslationModel;
use Exception;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DockerCommandService
{
    public const WORKING_DIRECTORY = 'app/data';

    public const CORRECTION_ATTEMPTS = 10;

    private ChatGPTService $chatGPTService;

    public function __construct(ChatGPTService $chatGPTService)
    {
        $this->chatGPTService = $chatGPTService;
    }

    /**
     * @throws Exception
     */
    public function executeCommand(
        string $command,
        string $originalCommand,
        int    $iteration = 0
    ): array
    {
        if ($iteration >= self::CORRECTION_ATTEMPTS) {
            throw new Exception('Correction attempts exceeded');
        }

        //@todo прибрати try/catch та $error
        $error = false;


        try {
            $process = new Process(explode(' ', $command));
            $process->setWorkingDirectory(storage_path(self::WORKING_DIRECTORY));
            $process->run();
        } catch (Exception $e) {
            $error = true;
        }


        if (!$process->isSuccessful() || $error) {
            $translatedCommand = $this->chatGPTService->correctCommand(
                $originalCommand,
                $command,
                $process->getErrorOutput()
            );

            if (str_contains('error: ', $translatedCommand)) {
                throw new Exception($translatedCommand);
            }

            return $this->executeCommand(
                $translatedCommand,
                $originalCommand,
                $iteration + 1
            );
        }

        return [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput(),
            'error' => $process->isSuccessful() ? null : $process->getErrorOutput(),
        ];
    }

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
}
