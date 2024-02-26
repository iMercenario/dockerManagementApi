<?php

namespace App\Http\Controllers;

use App\Services\DockerCommandService;
use Illuminate\Http\Request;

class DockerCommandController extends Controller
{
    public function executeCommand(Request $request, DockerCommandService $service)
    {
        $translatedCommand = $service->translateCommand($request->input('command'));

        return response()->json([
            'output' => $translatedCommand,
        ]);
    }
}
