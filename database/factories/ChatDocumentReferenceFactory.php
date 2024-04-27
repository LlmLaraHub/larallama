<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChatDocumentReference>
 */
class ChatDocumentReferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_id' => Chat::factory(),
            'document_id' => Document::factory(),
            'document_chunk_id' => DocumentChunk::factory(),
            'reference' => fake()->text(),
        ];
    }
}
