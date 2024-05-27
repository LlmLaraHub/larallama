<?php

namespace Tests\Feature;

use Tests\TestCase;
use Webklex\IMAP\Facades\Client;
use Webklex\PHPIMAP\Client as ConcreteClient;

class ClientTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_triggers_job(): void
    {

        $this->markTestSkipped('@TODO more mocking');
        $client = new ConcreteClient();
        Client::shouldReceive('account->connect')
            ->once()
            ->andReturn($client);

        (new \App\Domains\EmailParser\Client())->handle();

    }
}
