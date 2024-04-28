<?php

namespace Database\Factories;

use App\Models\DocumentChunk;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MessageDocumentReference>
 */
class MessageDocumentReferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_id' => Message::factory(),
            'document_chunk_id' => DocumentChunk::factory(),
            'distance' => 11.321851037400464,
        ];
    }
}
