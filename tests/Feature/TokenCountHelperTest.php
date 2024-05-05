<?php

namespace Tests\Feature;

use App\Helpers\TokenCountHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use SundanceSolutions\LarachainTokenCount\Facades\LarachainTokenCount;
use Tests\TestCase;
use Yethee\Tiktoken\EncoderProvider;

class TokenCountHelperTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_token_counter(): void
    {
        $content = get_fixture("token_count_huge.txt", false);
        $larachainToken =  LarachainTokenCount::count($content);
        $provider = new EncoderProvider();
        $encoder = $provider->getForModel('gpt-3.5-turbo-0301');
        $tokens = $encoder->encode($content);
        $test3 = token_counter_v2($content);
        dd(collect($tokens)->count(), $larachainToken, 13977, $test3);
        //13966,12502,13977
        $this->assertEquals(13977, TokenCountHelper::countTokens($content));
    }
}
