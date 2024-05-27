<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Jetstream\Events\InvitingTeamMember;

class InvitingTeamMemberAddToSystemListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(InvitingTeamMember $event): void
    {
        $invitedUser = User::firstOrCreate([
            'email' => $event->email,
        ], [
            'name' => str($event->email)->before('@')->headline()->toString(),
            'password' => bcrypt(Str::random(12)),
        ]);

        $invitedUser->current_team_id = $event->team->id;
        $invitedUser->updateQuietly();

        if (! $invitedUser->belongsToTeam($event->team)) {
            $event->team->users()->attach(
                $invitedUser, ['role' => $event->role]
            );
        }
    }
}
