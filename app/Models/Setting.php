<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $key
 * @property string $title
 * @property string $description
 * @property bool $active
 * @property array $secrets
 * @property array $meta_data
 */
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

    public static function getLlms(): array
    {
        return collect(Setting::createNewSetting()->secrets)
            ->filter(function ($value, $key) {
                $api_key = data_get($value, 'api_key');

                return ! is_null($api_key);
            })
            ->keys()->toArray();
    }

    public static function getAllActiveLlms(): array
    {
        $settings = Setting::getLlms();

        return collect(Setting::getAllLlms())->filter(function ($value, $key) use ($settings) {
            return in_array($value['key'], $settings);
        })->values()->toArray();
    }

    public static function getAllActiveLlmsWithEmbeddings(): array
    {
        return collect(Setting::getAllActiveLlms())->filter(function ($value, $key) {
            return $value['has_embed'];
        })->values()->toArray();
    }

    public static function getAllLlms(): array
    {
        return [
            [
                'key' => 'mock',
                'title' => 'Mock LLM',
                'description' => 'This will mock all the LLM features great for local development',
                'has_embed' => false,
            ],
            [
                'key' => 'openai',
                'title' => 'OpenAi',
                'description' => 'This will work with the OpenAi Api',
                'has_embed' => true,
            ],
            [
                'key' => 'claude',
                'title' => 'Claude',
                'description' => 'This will work with the Claude Api',
                'has_embed' => false,
            ],
            [
                'key' => 'mock',
                'title' => 'OpenAi Azure',
                'description' => 'This will work with the Azure OpenAi Api',
                'has_embed' => false,
            ],
            [
                'key' => 'ollama',
                'title' => 'Ollama',
                'description' => 'This will work with the Ollam API',
                'has_embed' => true,
            ],
            [
                'key' => 'groq',
                'title' => 'Groq',
                'description' => 'This will work with the Groq Api',
                'has_embed' => false,
            ],
            [
                'key' => 'gemini',
                'title' => 'Gemini',
                'description' => 'This will work with the Gemini Api',
                'has_embed' => false,
            ],
        ];
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
                        'models' => [
                            'completion_model_default' => 'gpt-3.5-turbo',
                            'completion_models' => [],
                            'chat_model_default' => 'gpt-3.5-turbo',
                            'chat_models' => [],
                            'embed_model_default' => 'text-embedding-3-large',
                            'embed_models' => [
                                'text-embedding-3-large',
                            ],
                        ],
                    ],
                    'claude' => [
                        'models' => [
                            'completion_model_default' => 'claude-3-haiku-20240307',
                            'completion_models' => [
                                'claude-3-opus-20240229',
                                'claude-3-sonnet-20240229',
                                'claude-3-haiku-20240307',
                            ],
                            'chat_model_default' => 'claude-3-haiku-20240307',
                            'chat_models' => [
                                'claude-3-opus-20240229',
                                'claude-3-sonnet-20240229',
                                'claude-3-haiku-20240307',
                            ],
                            'embed_model_default' => null,
                            'embed_models' => [],
                        ],
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
                        'api_key' => null,
                        'api_url' => 'http://localhost:11434/api/',
                    ],
                ],
            ]);
        } else {
            $setting = Setting::first();
        }

        return $setting;
    }

    public static function getDrivers(): array
    {
        $settings = Setting::createNewSetting();

        return [];
    }

    public static function secretsConfigured(): bool
    {
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
        $setting = Setting::createNewSetting();

        $secrets = data_get($setting->secrets, $driver, null);

        if (! $key) {
            return $secrets;
        }

        return data_get($secrets, $key, $default);
    }
}
