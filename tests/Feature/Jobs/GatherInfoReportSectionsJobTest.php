<?php

namespace Tests\Feature\Jobs;

use Tests\TestCase;

class GatherInfoReportSectionsJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
