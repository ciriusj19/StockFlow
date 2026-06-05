<?php

namespace App\Enums;

enum InventoryStatus: string
{
    case Draft = 'draft';
    case Validated = 'validated';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Validated => 'Validé',
        };
    }
}
