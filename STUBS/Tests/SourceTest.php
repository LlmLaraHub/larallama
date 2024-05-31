<?php

namespace Tests\Feature;

use App\Domains\EmailParser\EmailClientFacade;
use App\Domains\EmailParser\MailDto;
use App\Domains\Sources\SourceTypeEnum;
use App\Models\Source;
use Tests\TestCase;
use Webklex\PHPIMAP\Support\FolderCollection;

class [RESOURCE_CLASS_NAME]Test extends TestCase
{
    public function test_run()
    {

        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::[RESOURCE_CLASS_NAME],
            ]);

        $source->run();
    }
}
