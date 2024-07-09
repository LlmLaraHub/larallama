<?php

namespace Tests\Feature;

use Facades\App\Domains\Sources\GoogleSheetSource\GoogleSheetWrapper;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleSheetWrapperTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_test_feed(): void
    {
        Http::fake([
            'docs.google.com/*' => Http::response(get_fixture('google_sheets.txt', false)),
        ]);

        Http::preventStrayRequests();

        $results = GoogleSheetWrapper::handle('1lywLUfx3Kf7GBQRdQg6yclhVaaWUYM9BL17kfiQshvE', 'STRATEGIES', 'A1:Z10');

        $this->assertNotEmpty($results);

    }
}
