<?php

namespace Tests\Feature;

use App\LlmDriver\Helpers\TrimText as HelpersTrimText;
use Tests\TestCase;

class TrimTextTest extends TestCase
{
    public function test_trimming()
    {
        //https://github.com/vlad-ds/gptrim/blob/main/gptrim/gptrim.py
        $example = 'But don’t humans also have genuinely original ideas?” Come on, read a fantasy book. It’s either a Tolkien clone, or it’s A Song Of Ice And Fire. Tolkien was a professor of Anglo-Saxon language and culture; no secret where he got his inspiration. A Song Of Ice And Fire is just War Of The Roses with dragons. Lannister and Stark are just Lancaster and York, the map of Westeros is just Britain (minus Scotland) with an upside down-Ireland stuck to the bottom of it – wake up, sheeple! Dullards blend Tolkien into a slurry and shape it into another Tolkien-clone. Tolkien-level artistic geniuses blend human experience, history, and the artistic corpus into a slurry and form it into an entirely new genre. Again, the difference is how finely you blend and what spices you add to the slurry.';

        $trim = new HelpersTrimText();

        $results = $trim->handle($example);

        $this->assertStringNotContainsString('don’t', $results);
        //word count
        $this->assertEquals(73, str_word_count($results));
        $this->assertEquals(140, str_word_count($example));
    }
}
