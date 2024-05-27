<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

/**
 * @property int $user_id
 * @property User $user
 */
class LoginToken extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSignedUrlAttribute()
    {
        return URL::temporarySignedRoute('signed_url.login', now()
            ->addMinutes(30), ['token' => $this->token]);
    }
}
