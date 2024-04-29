<?php

namespace Tests;

use App\Domains\Agents\VerifyPromptOutputDto;
use App\Models\Team;
use App\Models\User;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LlmLaraHub\LlmDriver\HasDrivers;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;

    public function createUserWithCurrentTeam()
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->current_team_id = Team::first()->id;
        $user->save();

        return $user->refresh();
    }

    public function fakeVerify(HasDrivers $model) {
        VerifyResponseAgent::shouldReceive('verify')->once()->andReturn(
            VerifyPromptOutputDto::from(
                [
                    'chattable' => $model->getChat(),
                    'originalPrompt' => 'test',
                    'context' => 'test',
                    'llmResponse' => 'test',
                    'verifyPrompt' => 'This is a completion so the users prompt was past directly to the llm with all the context.',
                    'response' => 'verified yay!',
                ]
            ));
    }
}
