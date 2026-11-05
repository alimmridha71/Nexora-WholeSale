<?php

namespace Tests\Unit;

use App\Enums\QuoteStatus;
use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class QuoteStatusTest extends TestCase
{
    public function test_closed_statuses_have_no_transitions(): void
    {
        foreach ([QuoteStatus::Approved, QuoteStatus::Rejected, QuoteStatus::Expired] as $status) {
            $this->assertFalse($status->isOpen());
            $this->assertSame([], $status->allowedTransitions());
        }
    }

    public function test_open_statuses_cannot_move_backwards(): void
    {
        $this->assertNotContains(QuoteStatus::Pending, QuoteStatus::UnderReview->allowedTransitions());
        $this->assertContains(QuoteStatus::Approved, QuoteStatus::Pending->allowedTransitions());
    }

    public function test_admin_is_never_a_registrable_role(): void
    {
        $this->assertNotContains(UserRole::Admin->value, UserRole::registrable());
        $this->assertFalse(UserRole::Admin->isBusinessBuyer());
    }
}
