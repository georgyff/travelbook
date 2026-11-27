<?php

namespace App\Enums;

/**
 * Who the trip was with.
 */
enum TripType: string
{
    case Solo = 'solo';
    case Couple = 'couple';
    case Friends = 'friends';
    case Family = 'family';
    case Mixed = 'mixed';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
