<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Retailer = 'retailer';
    case Wholesaler = 'wholesaler';
    case Distributor = 'distributor';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Roles a visitor may pick when self-registering (never admin).
     *
     * @return list<string>
     */
    public static function registrable(): array
    {
        return [self::Retailer->value, self::Wholesaler->value, self::Distributor->value];
    }

    public function isBusinessBuyer(): bool
    {
        return $this !== self::Admin;
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
