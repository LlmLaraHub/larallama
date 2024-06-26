<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TeamInviteAcceptControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_accepts_team_invitation(): void
    {
        $userInvited = User::factory()->create();

        $invite = TeamInvitation::factory()->create([
            'email' => $userInvited->email,
        ]);

        $url = URL::signedRoute('team-invitations.accept',
            [
                'invitation' => $invite,
            ]);

        $this->get($url);

        $this->assertAuthenticatedAs($userInvited);

        $this->assertDatabaseCount('team_invitations', 0);

        //make the user since this is how the system works
        //add that email to the invite
        //then make the user and visit there
    }
}
