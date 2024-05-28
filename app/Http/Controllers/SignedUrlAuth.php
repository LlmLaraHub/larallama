<?php

namespace App\Http\Controllers;

use App\Models\LoginToken;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SignedUrlAuth extends Controller
{
    use SendMagicTrait;

    public function create()
    {

        $model = config('magic-login.user_table');

        $validated = request()->validate(
            ['email' => 'required|email']
        );

        $user = $model::whereEmail($validated['email'])->first();

        if (! $user) {
            return response()->json([], 200);
        }

        $this->sendMagic($user, $validated['email']);

        return response()->json([], 200);
    }

    public function signInWithToken(Request $request, $token)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        try {
            $loginToken = LoginToken::query()
                ->whereToken($token)
                ->firstOrFail();

            $loginToken->consumed_at = now();
            $loginToken->save();

            Auth::login($loginToken->user);

            return redirect()->intended(config('magic-login.redirect'));
        } catch (ModelNotFoundException $e) {
            logger('Attempt at signed url');
            logger($e->getMessage());
            abort(401);
        }
    }
}
