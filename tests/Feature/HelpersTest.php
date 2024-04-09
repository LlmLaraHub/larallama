<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_config_helper(): void
    {
        
        $this->assertEquals(4096, driverHelper('mock', 'embedding_size.mock'));

    }
}
