<?php

namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamInviteAcceptController extends Controller
{
    public function accept(Request $request, TeamInvitation $invitation)
    {
        $user = User::where('email', $invitation->email)->first();
        $user->current_team_id = $invitation->team_id;
        $user->updateQuietly();
        Auth::login($user);

        $invitation->delete();

        return to_route('collections.index');
    }
}
