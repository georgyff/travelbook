<?php

namespace App\Enums;

/**
 * The owner's completion rule, shared by countries and places.
 * White = completed, yellow = partially completed, red = not completed.
 */
enum CompletionStatus: string
{
    case Completed = 'completed';
    case Partial = 'partial';
    case NotCompleted = 'not_completed';

    /**
     * The lower-case phrase used in sentences and screen-reader text.
     */
    public function label(): string
    {
        return match ($this) {
            self::Completed => 'completed',
            self::Partial => 'partially completed',
            self::NotCompleted => 'not completed',
        };
    }

    /**
     * The modifier used by the `.chip--*` classes.
     */
    public function chipModifier(): string
    {
        return match ($this) {
            self::Completed => 'completed',
            self::Partial => 'partial',
            self::NotCompleted => 'not',
        };
    }
}
