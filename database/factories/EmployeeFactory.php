<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $nikSeq = $this->faker->unique()->numberBetween(100, 999);

        return [
            'nik' => "EMP-2026-{$nikSeq}",
            'user_id' => User::factory(),
            'full_name' => $this->faker->name(),
            'position' => $this->faker->randomElement(['Staff Dokumen Pabean', 'Staff Delivery Order', 'Staff Traffic', 'Finance Staff', 'Field Officer']),
            'team' => $this->faker->randomElement(['CUSTOMS_DOC', 'DO_SHIPPING', 'TRAFFIC_PORT', 'FINANCE', 'FIELD_OPS']),
            'start_date' => now()->subMonths(6)->toDateString(),
            'status' => 'ACTIVE',
            'base_salary' => $this->faker->randomFloat(2, 6000000, 15000000),
            'bank_name' => 'Bank Mandiri',
            'bank_account_number' => $this->faker->numerify('12000########'),
            'bank_account_holder' => $this->faker->name(),
            'bpjs_tk_number' => $this->faker->numerify('##########'),
            'bpjs_kes_number' => $this->faker->numerify('##########'),
            'tax_ptkp_status' => 'TK/0',
            'phone' => '08' . $this->faker->numerify('12########'),
        ];
    }
}
