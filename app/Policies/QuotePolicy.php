<?php

namespace App\Policies;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Controllers scope buyers to their own quotes.
    }

    public function view(User $user, Quote $quote): bool
    {
        return $user->isAdmin() || $quote->user_id === $user->id;
    }

    /**
     * Only business buyers (retailer / wholesaler / distributor) request quotes.
     */
    public function create(User $user): bool
    {
        return $user->role->isBusinessBuyer();
    }

    /**
     * Status changes are an admin action and only on open quotes.
     */
    public function update(User $user, Quote $quote): bool
    {
        return $user->isAdmin() && $quote->status->isOpen();
    }

    /**
     * Owners may withdraw a quote while it is still pending.
     */
    public function delete(User $user, Quote $quote): bool
    {
        return $quote->user_id === $user->id && $quote->status === QuoteStatus::Pending;
    }
}
