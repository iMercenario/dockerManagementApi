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

        $input = [
            'database' => explode('.', $inputCollectionName)[0],
            'collection' => explode('.', $inputCollectionName)[1],
        ];

        $output = [
            'database' => explode('.', $outputCollectionName)[0],
            'collection' => explode('.', $outputCollectionName)[1],
        ];

        $inputCollection = $this->mongoDB->selectCollection($input['database'], $input['collection']);
        $outputCollection = $this->mongoDB->selectCollection($output['database'], $output['collection']);

        foreach ($inputCollection->find() as $doc) {

            $inputCommand = $doc['input'];
            $outputCommand = $doc['output'];

            if (!empty($outputCollection->find(['input' => $inputCommand])->toArray()))
            {
                $this->warn('комманду ' . $inputCommand . ' вже було додано');
                continue;
            }

            sleep(1);


            $validatedCommand = $this->validateAndGenerateCommand($inputCommand, $outputCommand);

            if ($validatedCommand !== null) {
                $outputCollection->insertOne([
                    'input' => $inputCommand,
                    'output' => $validatedCommand,
                ]);
                $this->info("Додано нову команду: $validatedCommand");
            }
        }
    }

    protected function validateAndGenerateCommand($input, $output, $attempt = 0)
    {
        $maxAttempts = 5;

        if ($attempt >= $maxAttempts) {
            $this->error("Перевищено максимальну кількість спроб для команди: $input");
            return null;
        }

        $isValid = $this->chatGPTService->validateCommand($input, $output);
        if ($isValid) {
            return $output;
        } else {
            $newCommand = $this->chatGPTService->correctCommand($input, $output, '');
            return $this->validateAndGenerateCommand($input, $newCommand, $attempt + 1);
        }
    }
}
