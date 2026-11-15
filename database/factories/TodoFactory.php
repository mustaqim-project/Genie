<?php

namespace Database\Factories;

use App\Models\Shipment;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoFactory extends Factory
{
    protected $model = Todo::class;

    public function definition(): array
    {
        return [
            'title' => 'Tugas: ' . $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'priority' => $this->faker->randomElement(['LOW', 'MEDIUM', 'HIGH', 'URGENT']),
            'status' => 'TODO',
            'due_date' => now()->addDays(3)->toDateString(),
            'assigned_to_user_id' => User::factory(),
            'created_by_user_id' => User::factory(),
            'shipment_id' => null,
            'category' => $this->faker->randomElement(['OPERATIONAL', 'CUSTOMS', 'FINANCE', 'SALES', 'GENERAL']),
        ];
    }
}
