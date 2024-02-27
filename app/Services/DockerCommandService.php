<?php

namespace App\Services;

use App\Models\TranslationModel;
use Exception;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DockerCommandService
{
    public const WORKING_DIRECTORY = 'app/data';

    public const CORRECTION_ATTEMPTS = 20;

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

        $isValid = $this->chatGPTService->validateCommand($originalCommand, $command);

        //@todo прибрати try/catch та $error
        $error = false;

        if ($isValid) {
            try {
//                $process = new Process(explode(' ', $command));
                $process = new Process(['bash', '-c', $command]);
                $process->setWorkingDirectory(storage_path(self::WORKING_DIRECTORY));
                $process->run();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        if (!$isValid  || $error || !$process->isSuccessful()) {

            if (isset($process) && $process->getErrorOutput()) {
                $error = $process->getErrorOutput();
            }

            $command = $this->chatGPTService->correctCommand(
                $originalCommand,
                $command,
                $error
            );

            if (str_contains('error: ', $command)) {
                throw new Exception($command);
            }

            return $this->executeCommand(
                $command,
                $originalCommand,
                $iteration + 1
            );
        }

        return [
            'original_command' => $originalCommand,
            'translated_command' => $command,
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
