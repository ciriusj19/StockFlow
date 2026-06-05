<?php

namespace App\Enums;

enum AlertStatus: string
{
    case New = 'new';
    case Viewed = 'viewed';
    case Resolved = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Nouvelle',
            self::Viewed => 'Consultée',
            self::Resolved => 'Résolue',
        };
    }
}
