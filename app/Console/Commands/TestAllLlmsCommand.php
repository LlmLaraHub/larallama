<?php

namespace App\Console\Commands;

use App\Domains\Messages\RoleEnum;
use App\Models\Setting;
use Facades\App\Domains\Sources\EmailSource;
use App\Models\Source;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use App\Domains\EmailParser\MailDto;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use Illuminate\Console\Command;

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
        $prompt = <<<PROMPT
What do you know about Laravel
PROMPT;

        $messages = [];
        $messages[] = MessageInDto::from([
            "role" => RoleEnum::User->value,
            "content" => $prompt
        ]);
        $drivers = ["groq", "openai", "claude"];
        $content = [];
        foreach ($drivers as $driver) {
            $results = LlmDriverFacade::driver($driver)->chat($messages);
            $content[] = [
                'driver' => $driver,
                'content' => $results->content,
            ];
            $this->info("Driver: " . $driver . " >>> " . $results->content);
        }

        put_fixture('test_all_llms.json', $content);

        $this->info('Done see file at ' . base_path('tests/fixtures/test_all_llms.json'));
    }
}
