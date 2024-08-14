<?php

namespace App\Domains\Events;

enum EventTypes: string
{
    case Event = 'event';
    case Task = 'task';
}
