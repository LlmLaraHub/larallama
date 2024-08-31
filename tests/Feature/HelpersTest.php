<?php

namespace Tests\Feature;

use App\Models\DocumentChunk;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_config_helper(): void
    {

        $this->assertEquals('mock', driverHelper('mock', 'models.embedding_model'));

    }

    public function test_clear_string()
    {
        $messystring = <<<HERE
        \u0018\n\b\u001b\f\f\u0019\u0011\f\u0014\u0007\f\u0011\u0011\u0016\b\u0006\u001a\u0011\u0005\u0016\u0014\u0016\u0004\u001c\u001c\u0011\u0005\u0005\u0019\u0003\u000f\u0011\u0016\b\u001b\u001c\u000b\u0011\u0017\u001e\u0005\n\u0002\u0001\u0002\u0016\u0007\u0018\u001d\u0017\u001e\f\u0006\u0016\u0014\u0016\u0013\u0012\u0011\u0016\u0004\f\u001a\u0016\u001b\u0017\u001e\u0016\u0011\u001c\u0012\u001b\u0017\u0019\u001c\u0005\u0016\u000e\u0016\n\u0011\u001b\u001e\u001b\u0003\u000f\u0011\u0016\u0018\u001e\u0011\n\u0015\u0011\u001c\u001d\f\u0019\u001a\u0006\u0016\u0018\f\u0017\u0011\f\u0016\u0014\u0016\u0015\u001c\u0012\f\u0018\u001e\u0019\u0017\u0011\f\u0005\u0016\b\u001b\u001c\u000b\u001d\n\u0016\u0007\u0011\u0011\u001a\u0016\u0014\u0016\u0001\u0011\u001e\u0006\u0016\u001b\u001b\f\f\u0005\u0016\u0004\u001e\u0019\u001c\u0011\n\u001b\u001e\u001b\f\u0016\u0014\u0016\u0004\u001b\u001c\u0012\u0011\u0016\u001b\u000e\u000b\u001b\u0016\u0016\u0002\u0001\u0002\u0016\u0011\u0005\u0019\u000f\u0019\u0011\u0017\u001c\u0011\u0016\n\u001b\u0017\u001e\u0016\u0015\u001c\u001b\u000f\u0011\u0016\u0019\u0017\u0016\u001b\u0016\b\u0018\n\u0011\u001b\u001e\u001b\u0003\u000f\u0011\u0016\u0018\u001e\u0011\u0016\u0014\u0016\u0015\u0011\u001c\u001d\f\u0019\u0017\u0016\u0002\u0001\u0002\u0016\u0004\u000f\u0019\u001c\u001b\u001a\u0019\u0018\u0017\u0005\u0016\n\u0004\u0016 \u0011\u0011\u0016 \u0019\u0011\u0016\u0014\u0016\u0002\u001b\f\u001a\u0016\u0007\u0018\u001d\f\u0016\u0014\u0016\u0017\u001c\u0019\u001e\u0011\u0017\u001a\u0005\u0016\u0004\u0017\u001e\u0016\u001f\u001e\u001d\u001c\u001b\u001a\u0019\u0018\u0017\n\u0007\u0019\u0017\u001b\u000f\u000f\u0006\u0016\u0014\u0016\u0011\u001b\u000f\u0016\u0018\u001d\u0017\u0019\u001a\u0006\nA Non Obvi \u0019\u0018\u0017\u0005\u0016\n\u0004\u0016 \u0011\u0011\u0016 \u0019\u0011\u0016\u0014\u0016\u0002\u001b\f\u001a\u0016\u0007\u0018\u001d\f\u0016\u0014\u0016\u0017\u001c\u0019\u001e\u0011\u0017\u001a\u0005\u0016\u0004\u0017\u001e\u0016\u001f\u001e\u001d\u001c\u001b\u001a\u0019\u0018\u0017\n\u0007\u0019\u0017\u001b\u000f\u000f\u0006\u0016\u0014\u0016\u0011\u001b\u000f\u0016\u0018\u001d\u0017\u0019\u001a\u0006\nA Non Obvious Separation of Concerns
        HERE;

        $results = cleanString($messystring);

        $this->assertStringNotContainsString('\u001b', $results);
    }

    public function test_append_prompt(): void
    {
        $prompt = 'Foobar';
        $this->assertStringContainsString(
            '[TOKEN]',
            append_prompt('[TOKEN]', $prompt)
        );

        $prompt = 'Foobar [TOKEN]';
        $this->assertEquals($prompt, append_prompt('[TOKEN]', $prompt));
    }

    public function test_clean_email()
    {

        $string = 'Alfred Nutile <alfrednutile@gmail.com>';
        $slug = clean_email($string);

        $this->assertEquals('alfrednutile@gmail.com', $slug);

        $string = 'alfrednutile@gmail.com';
        $slug = clean_email($string);

        $this->assertEquals('alfrednutile@gmail.com', $slug);
    }

    public function test_email_slug()
    {

        $string = 'assistant+P4FeXmoVOzcE@laralamma.ai';
        $slug = slug_from_email($string);

        $this->assertEquals('P4FeXmoVOzcE', $slug);

        $string = 'assistant+P4FeXmoVOzcE@laralamma.ai <assistant+foo@bar.com>';
        $slug = slug_from_email($string);

        $this->assertEquals('P4FeXmoVOzcE', $slug);
    }

    public function test_fixes_text()
    {
        // Create a string with an invalid UTF-8 character sequence
        $text = 'An endemic state is defined as an incidence of '.chr(0x80);

        $results = to_utf8($text);

        // Check that the invalid character has been removed
        $this->assertEquals('An endemic state is defined as an incidence of ', $results);
    }

    public function test_chunks_a_string()
    {
        $maxLength = 100;

        $string = <<<'STRING'
        Cupidatat nostrud anim consectetur veniam enim excepteur ut aliqua Lorem nulla non. Pariatur eu sunt quis. Consectetur fugiat Lorem reprehenderit reprehenderit id sunt labore duis irure laboris officia. In labore culpa nulla. Reprehenderit non eu reprehenderit. Culpa irure voluptate consequat consequat. Esse aliquip ipsum culpa tempor nostrud esse laborum eiusmod est laborum.

        Mollit do minim ullamco exercitation sint in laboris pariatur ea Lorem Lorem. Est sunt sit pariatur in elit nisi id incididunt eiusmod ipsum Lorem commodo ut incididunt. Enim et dolor consequat adipisicing mollit dolor officia dolore. Quis aute dolor enim ullamco exercitation dolor minim laboris mollit fugiat velit. Sit deserunt dolor ea. In ipsum ipsum reprehenderit pariatur sit reprehenderit velit anim nostrud nostrud sit velit. Adipisicing ad laboris excepteur in veniam magna dolore aute excepteur dolore nostrud adipisicing excepteur laborum ut. Minim ea officia duis aliquip eiusmod qui ex incididunt eiusmod.

        Veniam officia labore enim eu laboris et voluptate velit. Id do velit adipisicing ex. Consectetur do amet et exercitation duis laboris. Irure labore ipsum et sint labore ex sunt aute magna aute.

        Qui ipsum aute laborum quis ea officia in sit ut. Minim occaecat aliquip enim officia ut pariatur. Tempor officia pariatur officia adipisicing excepteur velit irure irure ex adipisicing aliquip ea culpa aute nulla. Ut ad veniam est nostrud consectetur. Elit commodo ea non aliqua aliquip id consectetur tempor sit aliqua minim eiusmod consequat sit do.
STRING;

        $chunks = chunk_string($string, $maxLength);

        $this->assertCount(9, $chunks);

    }

    public function test_get_embedding_size(): void
    {

        $model = DocumentChunk::factory()->create();

        $embedding_column = get_embedding_size($model->getEmbeddingDriver());

        $this->assertEquals('embedding_4096', $embedding_column);

        $model = DocumentChunk::factory()
            ->openAi()
            ->create();

        $embedding_column = get_embedding_size($model->getEmbeddingDriver());

        $this->assertEquals('embedding_3072', $embedding_column);

    }

    public function test_calculate_dynamic_threshold()
    {
        $distances = [0.5, 0.75, 2, 3, 10];

        $threshold = calculate_dynamic_threshold($distances);

        $this->assertEquals(3.25, $threshold);
    }

    public function test_get_latest_user_content(): void
    {
        $messageArray = [
            MessageInDto::from([
                'role' => 'user',
                'content' => 'test',
            ]),
            MessageInDto::from([
                'role' => 'assistant',
                'content' => 'test2',
            ]),
            MessageInDto::from([
                'role' => 'user',
                'content' => 'test3',
            ]),
        ];

        $this->assertEquals('test3', get_latest_user_content($messageArray));
    }
}
