<?php

declare(strict_types=1);

namespace App\Enum;

enum SlotStatus: string
{
    case Available = 'available';
    case Held = 'held';
    case Booked = 'booked';
    case Blocked = 'blocked';
}
