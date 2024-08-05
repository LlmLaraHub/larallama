<?php

namespace App\Domains\Messages;

enum RoleEnum: string
{
    case User = 'user';
    case System = 'system';
    case Assistant = 'assistant';
    case Tool = 'tool';
}
