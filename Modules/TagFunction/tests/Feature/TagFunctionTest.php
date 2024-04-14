<?php

namespace LlmLaraHub\TagFunction\tests\Feature;

use Tests\TestCase;

class TagFunctionTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
