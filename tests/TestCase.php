<?php

namespace Tests;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;


    public function createUserWithCurrentTeam() {
        $user = User::factory()->withPersonalTeam()->create();
        $user->current_team_id = Team::first()->id;
        $user->save();

        return $user->refresh();
    }
}
