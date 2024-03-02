<?php

namespace App\Console\Commands;

use App\Services\ChatGPTService;
use Illuminate\Console\Command;
use MongoDB\Client as MongoDB;

/**
 * Class GenerateDatasetCommand
 */
class GenerateDatasetCommand extends Command
{
    protected $signature = 'generate:dataset {inputDatabaseCollection} {outputDatabaseCollection}';
    protected $description = 'Розширює датасет для навчання моделі, використовуючи ChatGPT API';

    private ChatGPTService $chatGPTService;
    private MongoDB $mongoDB;

    public function __construct(ChatGPTService $chatGPTService)
    {
        parent::__construct();
        $this->chatGPTService = $chatGPTService;
        $this->mongoDB = new MongoDB();
    }

    /**
     * Execute the console command.
     */
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

            sleep(1);


            $commands = $this->generateNewCommands($inputCommand);


            foreach ($commands as $command) {

                $validatedCommand = $this->validateAndGenerateCommand($command, '');

                if ($validatedCommand !== null && !empty($validatedCommand)) {
                    $outputCollection->insertOne([
                        'input' => $command,
                        'output' => $validatedCommand,
                    ]);
                    $this->info("Додано нову команду: $validatedCommand");
                }
            }
        }
    }

    protected function validateAndGenerateCommand($input, $output, $attempt = 0)
    {
        $maxAttempts = 50;

        $this->info("Attempt: {$attempt} Input: {$input} Output:{$output}\n")   ;

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

    protected function generateNewCommands($input)
    {
        $text = $this->chatGPTService->generateCommand($input);

        return json_decode($text, true);
    }
}
