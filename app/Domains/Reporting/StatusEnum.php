<?php

namespace App\Domains\Reporting;

enum StatusEnum: string
{
    case Pending = 'pending';
    case Complete = 'complete';
    case Cancelled = 'cancelled';
    case CompleteWithErrors = 'complete_with_errors';
    case Running = 'running';
}
