<?php

namespace App\Enums;

/**
 * Where a trip stands, from first idea to done. Cases are in that order, which is also the sort order.
 */
enum TripStatus: string
{
    case Idea = 'idea';
    case Booked = 'booked';
    case PartiallyBooked = 'partially_booked';
    case Ongoing = 'ongoing';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Idea => 'Idea',
            self::Booked => 'Booked',
            self::PartiallyBooked => 'Partially booked',
            self::Ongoing => 'Ongoing',
            self::Done => 'Done',
        };
    }

    /**
     * Position in the idea → done sequence, for sorting.
     */
    public function rank(): int
    {
        return array_search($this, self::cases(), true);
    }
}
