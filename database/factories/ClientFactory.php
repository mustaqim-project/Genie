<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $codeNum = $this->faker->unique()->numberBetween(100, 999);
        return [
            'code' => "CLI-{$codeNum}",
            'name' => 'PT ' . strtoupper($this->faker->company()) . ' INDONESIA',
            'npwp' => $this->faker->numerify('##.###.###.#-###.000'),
            'nib' => $this->faker->numerify('912000#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '021-' . $this->faker->numerify('5890####'),
            'pic_name' => $this->faker->name(),
            'address' => $this->faker->address(),
            'is_active' => true,
            'surat_kuasa_expired_at' => now()->addMonths(12),
        ];
    }
}
