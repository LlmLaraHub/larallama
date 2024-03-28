<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('collection.{id}', function ($user, $id) {
    Log::info("Connecting to channel collection." . $id);
    /**
     * @TODO
     * Must be on the team of the collection!
     */
    return true;
});