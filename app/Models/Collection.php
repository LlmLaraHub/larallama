<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LlmLaraHub\LlmDriver\DriversEnum;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\TagFunction\Contracts\TaggableContract;
use LlmLaraHub\TagFunction\Helpers\Taggable;
use Filament\Forms;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Class Project
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property DriversEnum $driver
 * @property DriversEnum $embedding_driver
 * @property bool $active
 * @property int $team_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Collection extends Model implements HasDrivers, TaggableContract
{
    use HasFactory;
    use Taggable;

    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'driver' => DriversEnum::class,
        'embedding_driver' => DriversEnum::class,
    ];

    public function getChatable(): HasDrivers
    {
        return $this;
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public static function getForm($teamId = null): array
    {
        return [
            Section::make('Collection')
                ->collapsible()
                ->description('Here you can centralize documents that you want to chat with')
                ->icon('heroicon-o-information-circle')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->placeholder('Enter the name of the collection')
                        ->required(),
                    Select::make('team_id')
                        ->columnSpan(2)
                        ->hidden(function () use ($teamId) {
                            return $teamId !== null;
                        })
                        ->relationship('team', 'name')
                        ->required(),                        
                    MarkdownEditor::make('description')
                        ->columnSpanFull()
                        ->label('Description')
                        ->placeholder('Enter the description of the collection this will help with the LLM ')
                        ->required(),
                    Toggle::make('active')
                        ->label('Active')
                        ->default(true),
                ]),

        ];
    }

    public function getChat(): Chat
    {
        /**
         * @TODO
         * I need to come back to this
         */
        return $this->chats()->first();
    }

    public function getDriver(): string
    {
        return $this->driver->value;
    }

    public function siblingTags(): array
    {
        return [];
    }

    public function getSummary(): string
    {
        return $this->description;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return Collection::class;
    }

    public function getEmbeddingDriver(): string
    {
        return $this->embedding_driver->value;
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function chats(): MorphMany
    {
        return $this->morphMany(Chat::class, 'chatable');
    }

    public function systemPrompt(): string
    {
        $systemPrompt = config('llmlarahub.collection.system_prompt');
        $prompt = <<<EOD
{$systemPrompt}: 
{$this->description}
EOD;

        return $prompt;
    }
}
