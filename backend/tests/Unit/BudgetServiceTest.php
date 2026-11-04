<?php

namespace Tests\Unit;

use App\Support\BudgetService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class BudgetServiceTest extends TestCase
{
    private function r(string $type, int $day, string $on): array
    {
        [$s, $e] = BudgetService::range($type, $day, Carbon::parse($on));

        return [$s->toDateString(), $e->toDateString()];
    }

    public function test_weekly_starts_on_monday(): void
    {
        $this->assertSame(['2026-11-02', '2026-11-08'], $this->r('weekly', 1, '2026-11-04'));
        $this->assertSame(['2026-11-02', '2026-11-08'], $this->r('weekly', 1, '2026-11-08'));
        $this->assertSame(['2026-11-09', '2026-11-15'], $this->r('weekly', 1, '2026-11-09'));
    }

    public function test_monthly_from_day_one(): void
    {
        $this->assertSame(['2026-11-01', '2026-11-30'], $this->r('monthly', 1, '2026-11-04'));
        $this->assertSame(['2026-02-01', '2026-02-28'], $this->r('monthly', 1, '2026-02-15'));
    }

    public function test_monthly_from_payday(): void
    {
        $this->assertSame(['2026-10-25', '2026-11-24'], $this->r('monthly', 25, '2026-11-04'));
        $this->assertSame(['2026-11-25', '2026-12-24'], $this->r('monthly', 25, '2026-11-25'));
        $this->assertSame(['2025-12-25', '2026-01-24'], $this->r('monthly', 25, '2026-01-10'));
    }

    public function test_color_bands(): void
    {
        $this->assertSame('#22c55e', BudgetService::color(0));
        $this->assertSame('#22c55e', BudgetService::color(30));
        $this->assertSame('#eab308', BudgetService::color(60));
        $this->assertSame('#f97316', BudgetService::color(80));
        $this->assertSame('#ef4444', BudgetService::color(120));
    }

    public function test_labels(): void
    {
        $this->assertSame('Aman', BudgetService::label(20));
        $this->assertSame('Waspada', BudgetService::label(55));
        $this->assertSame('Hati-hati', BudgetService::label(75));
        $this->assertSame('Kritis', BudgetService::label(95));
        $this->assertSame('Lewat', BudgetService::label(101));
    }
}