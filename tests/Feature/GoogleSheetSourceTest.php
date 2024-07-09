<?php

namespace Tests\Feature;

use App\Domains\Sources\GoogleSheetSource;
use App\Domains\Sources\SourceTypeEnum;
use App\Jobs\ProcessCSVJob;
use App\Models\Source;
use Facades\App\Domains\Sources\GoogleSheetSource\GoogleSheetWrapper;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GoogleSheetSourceTest extends TestCase
{
    public function test_run()
    {

        Queue::fake();

        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::GoogleSheetSource,
            'meta_data' => [
                'sheet_id' => '1lywLUfx3Kf7GBQRdQg6yclhVaaWUYM9BL17kfiQshvE',
                'sheet_name' => 'STRATEGIES',
            ],
        ]);

        $this->assertDatabaseCount('documents', 0);
        $data = get_fixture('google_sheets.txt', false);

        GoogleSheetWrapper::shouldReceive('handle')->once()->andReturn($data);

        (new GoogleSheetSource())->handle($source);

        Queue::assertPushed(ProcessCSVJob::class);

    }
}
