<?php

namespace App\Http\Controllers;

use App\Services\ChatGPTService;
use App\Services\DockerCommandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DockerCommandController extends Controller
{
    public function executeCommand(Request $request, DockerCommandService $service): JsonResponse
    {
        $command = $request->input('command');
        $translatedCommand = $service->translateCommand($command);

        try {
            $output = $service->executeCommand($translatedCommand, $command);
        } catch (\Exception $e) {
            $output = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return response()->json($output);
    }
}
