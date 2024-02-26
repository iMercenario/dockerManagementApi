<?php

namespace App\Http\Controllers;

use App\Services\DockerCommandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DockerCommandController extends Controller
{
    public function executeCommand(Request $request, DockerCommandService $service): JsonResponse
    {
        $translatedCommand = $service->translateCommand($request->input('command'));

        try {
            $output = $service->executeCommand($translatedCommand);
        } catch (\Exception $e) {
            $output = [
                'type' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return response()->json([
            'translated_command' => $translatedCommand,
            'output' => $output,
        ]);
    }
}
