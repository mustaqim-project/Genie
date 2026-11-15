<?php

namespace Database\Factories;

use App\Models\AccountingPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountingPeriodFactory extends Factory
{
    protected $model = AccountingPeriod::class;

    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-1 year', '+1 month');
        $start = (clone $date)->modify('first day of this month');
        $end = (clone $date)->modify('last day of this month');

        return [
            'name' => 'Periode ' . $start->format('F Y'),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'is_closed' => false,
            'closed_at' => null,
            'closed_by_id' => null,
            'closing_notes' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_closed' => true,
            'closed_at' => now(),
            'closing_notes' => 'Tutup buku resmi bulanan terverifikasi',
        ]);
    }
}
