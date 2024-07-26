<?php

namespace Feature;

use App\Domains\Tokenizer\Templatizer;
use Tests\TestCase;

class TemplatizerTest extends TestCase
{
    public function test_context() {
        $templatizer = new Templatizer();
        $context = $templatizer->handle(
            'Hello [CONTEXT]',
            [
                '[CONTEXT]'
            ],
            'World!'
        );

        $this->assertEquals('Hello World!', $context);
    }

    public function test_default_tokens() {
        $replace = now()->startOfWeek()->format('m/d/Y');
        $replace2 = now()->endOfWeek()->format('M d, Y');
        $templatizer = new Templatizer();
        $context = $templatizer->handle(
            'Hello [START_WEEK] to [END_WEEK] [CONTEXT]',
            []
            ,
            'World!'
        );

        $this->assertStringContainsString($replace, $context);
        $this->assertStringContainsString($replace2, $context);
        $this->assertStringContainsString('World', $context);
    }

    public function test_append_context() {
        $templatizer = new Templatizer();

        $context = $templatizer->appendContext(true)
            ->handle(
            'Hello [START_WEEK] to [END_WEEK]',
            [],
            'World!',
            true
        );
        $this->assertStringContainsString('World', $context);

        $context = $templatizer->handle(
                'Hello [START_WEEK] to [END_WEEK]',
                [],
                'World!',
                true
            );
        $this->assertStringNotContainsString('World', $context);
    }

    public function test_text_replace() {
        $htmlResults = "text here";
        $somePrompt = "Some [CONTEXT] here [START_WEEK]";

        $template = new Templatizer();
        $prompt = $template->appendContext(true)
            ->handle($somePrompt, [], $htmlResults);

        $this->assertStringContainsString('Some text here', $prompt);
    }

    public function test_date_range() {
        $replace = now()->startOfWeek()->format('m/d/Y');
        $replace2 = now()->startOfWeek()->format('M d, Y');
        $templatizer = new Templatizer();
        $context = $templatizer->handle(
            'Hello [START_WEEK] to [END_WEEK]',
            [
                '[START_WEEK]',
                '[END_WEEK]'
            ]
            ,
            'World!'
        );

        $this->assertStringContainsString($replace, $context);
        $this->assertStringContainsString($replace2, $context);
    }

    public function test_get_tokens() {
        $tokens = Templatizer::getTokens();
        $this->assertIsArray($tokens);
        $this->assertTrue(in_array('CONTEXT', $tokens));
        $this->assertTrue(in_array('START_WEEK', $tokens));
        $this->assertTrue(in_array('END_WEEK', $tokens));
    }
}
