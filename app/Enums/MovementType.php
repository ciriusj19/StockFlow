<?php

namespace App\Enums;

enum MovementType: string
{
    case Entry = 'ENTRY';
    case Exit = 'EXIT';
    case Adjustment = 'ADJUSTMENT';

    public function label(): string
    {
        return match ($this) {
            self::Entry => 'Entrée',
            self::Exit => 'Sortie',
            self::Adjustment => 'Ajustement',
        };
    }
}
