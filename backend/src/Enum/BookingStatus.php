<?php

declare(strict_types=1);

namespace App\Enum;

enum BookingStatus: string
{
    case AwaitingTeacher = 'awaiting_teacher';
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
}
