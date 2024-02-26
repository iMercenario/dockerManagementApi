<?php

namespace App\Http\Controllers;

use App\Models\TranslationModel;
use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DockerCommandController extends Controller
{
    public function executeCommand(Request $request)
    {
        $command = $request->input('command');

        // Переклад команди (припускаємо, що ви вже маєте логіку для цього)
        $translatedCommand = $this->translateCommand($command);

        // Виконання команди
//        $process = new Process(explode(' ', $translatedCommand));
//        $process->run();

        return response()->json([
            'message' => 'Command translated successfully',
            'output' => $translatedCommand,
        ]);
//
//        // Перевірка на помилки
//        if (!$process->isSuccessful()) {
//            throw new ProcessFailedException($process);
//        }
//
//        return response()->json([
//            'message' => 'Command executed successfully',
//            'output' => $process->getOutput(),
//        ]);
    }

    protected function translateCommand(string $text)
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
