<?php

namespace Tests\Feature\Mail;

use App\Mail\OutputMail;
use Tests\TestCase;

class OutputMailTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_mail(): void
    {
        $mailable = new OutputMail(
            'foobar',
            'bazboo'
        );

        $mailable->assertSeeInHtml('foobar');
    }
}
