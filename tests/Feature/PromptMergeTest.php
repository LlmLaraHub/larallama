<?php

namespace Tests\Feature;

use App\Domains\Prompts\PromptMerge;
use Tests\TestCase;

class PromptMergeTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_one_token(): void
    {
        $token = ['[FOO]'];

        $contents = ['BAR'];

        $prompt = '[FOO]BAR';

        $results = PromptMerge::merge($token, $contents, $prompt);

        $this->assertEquals('BARBAR', $results);
    }
}
