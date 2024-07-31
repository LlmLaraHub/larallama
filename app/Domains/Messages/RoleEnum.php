<?php

namespace App\Domains\Messages;

enum git pRoleEnum: string
{
    case User = 'user';
    case System = 'system';
    case Assistant = 'assistant';
}
