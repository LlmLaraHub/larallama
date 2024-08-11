<?php

namespace App\Console\Commands;

use App\Domains\Messages\RoleEnum;
use Illuminate\Console\Command;
use LlmLaraHub\LlmDriver\Functions\ToolTypes;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;

class TestAllLlmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-all-llms-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Live testing all llms';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $prompt = <<<'PROMPT'
What do you know about Laravel in one sentence
PROMPT;

        $messages = [];
        $messages[] = MessageInDto::from([
            'role' => RoleEnum::User->value,
            'content' => $prompt,
        ]);
        $drivers = ['groq', 'openai', 'claude', 'ollama'];
        $content = [];
        foreach ($drivers as $driver) {
            if ($driver === 'ollama' && app()->environment() !== 'local') {
                $this->info('Skipping '.$driver.' since it is not local');

                continue;
            }

            try {
                $start_time = microtime(true);
                $this->info('Driver: '.$driver);
                $results = LlmDriverFacade::driver($driver)
                    ->setToolType(ToolTypes::NoFunction)
                    ->chat($messages);
                $end_time = microtime(true);
                $execution_time = $end_time - $start_time;
                $content[$driver] = [
                    'driver' => $driver,
                    'execution_time' => $execution_time,
                    'content' => str($results->content)->limit(75),
                ];
            } catch (\Exception $e) {
                $this->error('Error running '.$driver);
                $this->error($e->getMessage());
                break;
            }
        }

        $this->table(
            ['Driver', 'Time', 'Content'],
            $content,
        );

        foreach ($drivers as $driver) {
            if ($driver === 'ollama' && app()->environment() !== 'local') {
                $this->info('Skipping '.$driver.' since it is not local');

                continue;
            }

            $results = data_get($content, $driver);

            if (empty($results)) {
                $this->error('No results for driver: '.$driver);
                break;
            }
        }
    }
}
