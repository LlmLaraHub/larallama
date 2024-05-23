<?php

namespace Tests\Feature;

use App\Listeners\InvitingTeamMemberAddToSystemListener;
use App\Models\Team;
use App\Models\User;
use Laravel\Jetstream\Events\InvitingTeamMember;
use Tests\TestCase;

class InvitingTeamMemberAddToSystemListenerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_adds_user(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
        ]);

        $email = fake()->email;
        $event = new InvitingTeamMember($team, $email, 'admin');

        $listener = new InvitingTeamMemberAddToSystemListener();
        $listener->handle($event);

        $this->assertDatabaseCount('users', 2);

        $addedUser = User::whereEmail($email)->first();
        $this->assertNotNull($addedUser->current_team_id);

        $this->assertTrue($addedUser->belongsToTeam($team));
    }
}
