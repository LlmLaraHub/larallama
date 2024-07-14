<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')
                ->user();

            $userModel = auth()->user();

            $meta_data = $userModel->meta_data;

            $meta_data['google'] = [
                'token' => $user->token,
                'refresh_token' => $user->refreshToken,
                'expires_in' => $user->expiresIn,
                'avatar' => $user->getAvatar(),
                'email' => $user->getEmail(),
            ];
            $userModel->update([
                'meta_data' => $meta_data,
            ]);

            request()->session()->flash('flash.bannerStyle', 'Google Authentication Successful!');

            return to_route('collections.index');
        } catch (\Exception $e) {
            Log::error('Error authenticating with Google', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'raw' => $e->getTraceAsString(),
            ]);
            request()->session()->flash('flash.bannerStyle', 'danger');
            request()->session()->flash('flash.banner', 'Google Authentication Failed');

            return to_route('collections.index');
        }
    }
}
