<?php

namespace App\Services;

use App\Models\DockerCommand;

class DataCollectionService
{
    public function saveCommand(string $input, string $output): void
    {
        $existingCommand = DockerCommand::firstWhere('input', $input);
        if ($existingCommand) {
            return;
        }

        $dockerCommand = new DockerCommand([
            'input' => $input,
            'output' => $output
        ]);
        $dockerCommand->save();
    }
}
