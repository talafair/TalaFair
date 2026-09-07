<?php

namespace Tests\Unit;

use App\Models\Announcement;
use App\Services\PointsCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PointsCalculatorTest extends TestCase
{
    #[DataProvider('formulaCases')]
    public function test_it_applies_the_talafair_formula(
        bool $participated,
        bool $engaged,
        int $expected
    ): void {
        $event = new Announcement([
            'base_points' => 50,
            'confirmation_points' => 20,
            'weight_points' => 10,
        ]);

        $this->assertSame($expected, PointsCalculator::score($event, $participated, $engaged));
    }

    public static function formulaCases(): array
    {
        return [
            'no participation or engagement' => [false, false, 50],
            'confirmed attendance only' => [true, false, 50],
            'engagement only' => [false, true, 55],
            'confirmed early attendance' => [true, true, 55],
        ];
    }
}