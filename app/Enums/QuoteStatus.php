<?php

namespace App\Enums;

enum QuoteStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Expired = 'expired';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * A quote can still be changed or withdrawn only while it is open.
     */
    public function isOpen(): bool
    {
        return in_array($this, [self::Pending, self::UnderReview], true);
    }

    /**
     * Allowed transitions an admin may apply from the current status.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::UnderReview, self::Approved, self::Rejected, self::Expired],
            self::UnderReview => [self::Approved, self::Rejected, self::Expired],
            self::Approved, self::Rejected, self::Expired => [],
        };
    }

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
