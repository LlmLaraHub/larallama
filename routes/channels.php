<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('collection.{id}', function ($user, $id) {
    Log::info('Connecting to channel collection.'.$id);

    /**
     * @TODO
     * Must be on the team of the collection!
     */
    return true;
});

//private-collection.chat.reports.36
Broadcast::channel('collection.chat.reports.{id}', function ($user, $id) {
    Log::info('Connecting to channel Report Id '.$id);

    /**
     * @TODO
     * Must be on the team of the collection!
     */
    return true;
});

Broadcast::channel('collection.chat.{id}.{chatId}', function ($user, $id, $chatId) {
    Log::info('Connecting to channel collection.chat.'.$id.'.'.$chatId);

    /**
     * @TODO
     * Must be on the team of the collection!
     */
    return true;
});
