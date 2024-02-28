<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChatGPTService;
use MongoDB\Client as MongoDB;

class ExpandDatasetCommand extends Command
{
    protected $signature = 'expand:dataset {inputDatabaseCollection} {outputDatabaseCollection}';
    protected $description = 'Розширює датасет для навчання моделі, використовуючи ChatGPT API';

    private ChatGPTService $chatGPTService;
    private MongoDB $mongoDB;

    public function __construct(ChatGPTService $chatGPTService)
    {
        parent::__construct();
        $this->chatGPTService = $chatGPTService;
        $this->mongoDB = new MongoDB();
    }

    public function handle()
    {
        $inputCollectionName = $this->argument('inputDatabaseCollection');
        $outputCollectionName = $this->argument('outputDatabaseCollection');

        $inputCollection = $this->mongoDB->selectCollection(env('MONGO_DB_DATABASE'), $inputCollectionName);
        $outputCollection = $this->mongoDB->selectCollection(env('MONGO_DB_DATABASE'), $outputCollectionName);

        $documents = $inputCollection->find();

        foreach ($documents as $doc) {
            $inputCommand = $doc['input'];

            $validatedCommand = $this->validateAndGenerateCommand($inputCommand);

            if ($validatedCommand !== null) {
                $outputCollection->insertOne([
                    'input' => $inputCommand,
                    'output' => $validatedCommand,
                ]);
                $this->info("Додано нову команду: $validatedCommand");
            }
        }
    }

    protected function validateAndGenerateCommand($input, $attempt = 0)
    {
        $maxAttempts = 5;

        if ($attempt >= $maxAttempts) {
            $this->error("Перевищено максимальну кількість спроб для команди: $input");
            return null;
        }

        $isValid = $this->chatGPTService->validateCommand($input);
        if ($isValid) {
            return $input;
        } else {
            $newCommand = $this->chatGPTService->generateCommand($input);
            return $this->validateAndGenerateCommand($newCommand, $attempt + 1);
        }
    }
}
