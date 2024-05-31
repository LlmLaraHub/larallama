<?php

namespace Tests\Feature;

use App\Domains\Generators\Source\GeneratorRepository;
use App\Domains\Generators\TokenReplacer;
use Tests\TestCase;

class TokenReplacerTest extends TestCase
{
    public function test_replaces_tokens()
    {
        $generator = new GeneratorRepository();
        $generator->setup('Foo Bar', 'Some Response Type', 'Some Description', false);

        $content = base_path('STUBS/Controllers/ResponseType.php');

        $tokenReplacer = new TokenReplacer();

        $results = $tokenReplacer->handle($generator, $content);

        $this->assertStringNotContainsString('[RESOURCE_CLASS_NAME]', $results);
    }
}
