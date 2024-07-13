<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/spreadsheets', 'https://www.googleapis.com/auth/calendar'])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();

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
            // $user->token is the access token
            // $user->refreshToken is the refresh token
            // $user->expiresIn is the lifetime in seconds of the access token

            // Here you would typically save the user info and tokens to your database

            request()->session()->flash('flash.bannerStyle', 'Google Authentication Successful!');
            return to_route('collections.index');
        } catch (\Exception $e) {
            Log::error('Error authenticating with Google', [
                'error' => $e->getMessage(),
            ]);
            request()->session()->flash('flash.bannerStyle', 'danger');
            request()->session()->flash('flash.banner', 'Google Authentication Failed');
            return to_route('collections.index');
        }
    }
}
