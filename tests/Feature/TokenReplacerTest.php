<?php

namespace Tests\Feature;

use App\Domains\Generators\Source\GeneratorRepository;
use App\Domains\Generators\TokenReplacer;
use App\Domains\Sources\SourceTypeEnum;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TokenReplacerTest extends TestCase
{
    public function test_replaces_tokens()
    {
        $generator = new GeneratorRepository();
        $generator->setup('FooBarSource',
            'Some Response Type',
            'Some Description',
            false);

        $content = File::get(base_path('STUBS/Controllers/SourceController.php'));

        $tokenReplacer = new TokenReplacer();

        $results = $tokenReplacer->handle($generator, $content);

        $this->assertStringNotContainsString('[RESOURCE_CLASS_NAME]', $results);
        $this->assertStringNotContainsString('[RESOURCE_TITLE_NAME]', $results);
        $this->assertStringContainsString('Foo Bar Source', $results);
        $this->assertStringContainsString('FooBarSource', $results);
        $this->assertStringContainsString('Sources/FooBarSource/Show', $results);
        $this->assertStringContainsString('FooBarSourceController', $results);
        $this->assertStringContainsString('SourceTypeEnum::FooBarSource', $results);
    }

    public function test_replaces_tokens_controller_tests()
    {
        $generator = new GeneratorRepository();
        $generator->setup('FooBarSource',
            'Some Response Type',
            'Some Description',
            false);

        $content = File::get(base_path('STUBS/Tests/SourceControllerTest.php'));

        $tokenReplacer = new TokenReplacer();

        $results = $tokenReplacer->handle($generator, $content);

        $this->assertStringContainsString('SourceTypeEnum::FooBarSource', $results);
        $this->assertStringContainsString('FooBarSourceControllerTest', $results);
        $this->assertStringContainsString('collections.sources.foo_bar_source.store', $results);
    }

    public function test_replaces_tokens_vue_tests()
    {
        $generator = new GeneratorRepository();
        $generator->setup('FooBarSource',
            'Some Response Type',
            'Some Description',
            false);

        $content = File::get(base_path('STUBS/Vue/Sources/Source/Create.vue'));

        $tokenReplacer = new TokenReplacer();

        $results = $tokenReplacer->handle($generator, $content);

        $this->assertStringContainsString('collections.sources.foo_bar_source.store', $results);
    }
}
