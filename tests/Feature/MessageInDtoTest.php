<?php

namespace Tests\Feature;

use App\Domains\Chat\MetaDataDto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use Tests\TestCase;

class MessageInDtoTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_dto(): void
    {
        $dto = MessageInDto::from([
            'content' => 'test',
            'role' => 'user',
            'meta_data' => null,
        ]);

        $this->assertNull($dto->meta_data);

        $dto = MessageInDto::from([
            'content' => 'test',
            'role' => 'user',
            'meta_data' => MetaDataDto::from(
                [
                    'persona' => 'test',
                    'filter' => 'test',
                    'completion' => true,
                    'tool' => 'test',
                    'date_range' => 'test',
                    'input' => 'test',
                ]
            ),
        ]);

        $this->assertNotNull($dto->meta_data);
    }

    public function test_to_array(): void
    {
        /**
         * @NOTE
         * This helps the Clients not error out
         * due to a key they do not understand
         */
        $dto = MessageInDto::from([
            'content' => 'test',
            'role' => 'user',
            'meta_data' => MetaDataDto::from(
                [
                    'persona' => 'test',
                    'filter' => 'test',
                    'completion' => true,
                    'tool' => 'test',
                    'date_range' => 'test',
                    'input' => 'test',
                ]
            ),
        ]);

        $this->assertEquals([
            'content' => 'test',
            'role' => 'user'], $dto->toArray());
    }
}
