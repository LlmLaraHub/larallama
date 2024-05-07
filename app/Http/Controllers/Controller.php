<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatResource;
use App\Models\Collection;

abstract class Controller
{
    protected function getChatResource(Collection $collection)
    {
        $chatResource = $collection->chats()->where('user_id', auth()->user()->id)
            ->latest('id')
            ->first();

        if ($chatResource?->id) {
            $chatResource = new ChatResource($chatResource);
        }

        return $chatResource;
    }
}
