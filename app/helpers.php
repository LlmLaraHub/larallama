<?php

use App\Events\ChatUiUpdateEvent;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\Helpers\TrimText;

if (! function_exists('put_fixture')) {
    function put_fixture($file_name, $content = [], $json = true)
    {
        if (! File::exists(base_path('tests/fixtures'))) {
            File::makeDirectory(base_path('tests/fixtures'));
        }

        if ($json) {
            $content = json_encode($content, 128);
        }
        File::put(
            base_path(sprintf('tests/fixtures/%s', $file_name)),
            $content
        );

        return true;
    }
}

if (! function_exists('notify_ui')) {
    function notify_ui(HasDrivers $model, string $message)
    {
        try {
            ChatUiUpdateEvent::dispatch(
                $model->getChatable(),
                $model->getChat(),
                $message
            );
        } catch (\Exception $e) {
            Log::error('Error notifying UI', ['error' => $e->getMessage()]);
        }
    }
}

if (! function_exists('remove_ascii')) {
    function remove_ascii($string): string
    {
        return str_replace("\u2019", ' ', preg_replace('/[^\x00-\x7F]+/', '', $string));
    }
}

if (! function_exists('get_fixture')) {
    function get_fixture($file_name, $decode = true)
    {
        $results = File::get(base_path(sprintf(
            'tests/fixtures/%s',
            $file_name
        )));

        if (! $decode) {
            return $results;
        }

        return json_decode($results, true);
    }
}

if (! function_exists('reduce_text_size')) {
    function reduce_text_size(string $text): string
    {
        return (new TrimText())->handle($text);
    }
}

if (! function_exists('driverHelper')) {
    function driverHelper(string $driver, string $key): string
    {
        Log::info("driverHelper: {$driver} {$key}");

        return config("llmdriver.drivers.{$driver}.{$key}");
    }
}

if (! function_exists('get_embedding_size')) {
    function get_embedding_size(string $ebmedding_driver): string
    {
        $embeddingModel = driverHelper($ebmedding_driver, 'models.embedding_model');

        $size = config('llmdriver.embedding_sizes.'.$embeddingModel);

        if ($size) {
            return 'embedding_'.$size;
        }

        return 'embeding_3072';
    }
}
