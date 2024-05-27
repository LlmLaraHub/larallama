<?php

namespace App\Http\Controllers;

use App\Mail\MagicSignIn;
use App\Models\LoginToken;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;

trait SendMagicTrait
{
    public function sendMagic(User $user, string $email)
    {
        $loginToken = new LoginToken();
        $loginToken->token = Uuid::uuid4()->toString();
        $loginToken->user_id = $user->id;
        $loginToken->expires_at = now()->addMinutes(30);
        $loginToken->save();

        Mail::to($email)->queue(
            new MagicSignIn($loginToken)
        );
    }
}
