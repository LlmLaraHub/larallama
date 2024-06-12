<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'steps' => 'array',
        'meta_data' => 'array',
        'secrets' => 'encrypted:array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function createNewSetting(): Setting
    {
        if (! Setting::exists()) {
            $setting = Setting::create([
                'user_id' => auth()->user()?->id,
                'steps' => [
                    'setup_secrets' => false,
                ],
                'meta_data' => [
                    'openai' => [
                        'models' => [],
                    ],
                ],
                'secrets' => [
                    'openai' => [
                        'api_key' => null,
                        'api_url' => 'https://api.openai.com/v1',
                        'request_timeout' => 120,
                        'organization' => null,
                    ],
                    'claude' => [
                        'api_key' => null,
                    ],
                    'groq' => [
                        'api_key' => null,
                        'api_url' => 'https://api.groq.com/openai/v1/',
                    ],
                    'ollama' => [
                        'api_key' => 'ollama',
                        'api_url' => 'http://localhost:11434/api/',
                    ],
                ],
            ]);
        } else {
            $setting = Setting::first();
        }

        return $setting;
    }

    public static function getDrivers() : array {
        $settings = Setting::createNewSetting();

        return [];
    }

    public static function secretsConfigured() : bool {
        $settings = Setting::createNewSetting();

        return data_get($settings->steps, 'setup_secrets', false);
    }

    public static function updateStep(Setting $setting): Setting
    {
        $steps = $setting->steps;
        $steps['setup_secrets'] = true;
        $setting->steps = $steps;
        $setting->save();

        return $setting;
    }

    public static function getSecret(
        string $driver,
        ?string $key = null,
        ?string $default = null
    ) {
        $setting = Setting::first();

        if (! $setting) {
            /**
             * @TODO
             * Throw an exception here
             */
            return config('llmdriver.drivers.'.$driver);
        }

        $secrets = data_get($setting->secrets, $driver, null);

        if (! $key) {
            return $secrets;
        }

        return data_get($secrets, $key, $default);
    }
}
