<?php

namespace Tests\Feature;

use App\Domains\EmailParser\EmailClientFacade;
use App\Domains\EmailParser\MailDto;
use App\Domains\Sources\SourceTypeEnum;
use App\Models\Source;
use Tests\TestCase;
use Webklex\PHPIMAP\Support\FolderCollection;

class WebPageSourceTest extends TestCase
{
    public function test_run()
    {

        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::WebPageSource,
            ]);

        $source->run();
    }
}
