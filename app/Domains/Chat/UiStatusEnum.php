<?php

namespace App\Domains\Chat;

enum UiStatusEnum: string
{
    case Complete = 'complete';
    case InProgress = 'in_progress';
    case NotStarted = 'not_started';
}
