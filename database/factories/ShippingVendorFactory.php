<?php

namespace Database\Factories;

use App\Models\ShippingVendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingVendorFactory extends Factory
{
    protected $model = ShippingVendor::class;

    public function definition(): array
    {
        $codeNum = $this->faker->unique()->numberBetween(10, 99);
        $name = $this->faker->randomElement(['MAERSK LINE', 'ONE OCEAN NETWORK', 'COSCO SHIPPING', 'WAN HAI LINES', 'CMA CGM', 'EVERGREEN']);

        return [
            'code' => "VND-SHP-{$codeNum}",
            'name' => "PT {$name} INDONESIA",
            'pic_name' => $this->faker->name(),
            'pic_email' => $this->faker->unique()->safeEmail(),
            'pic_phone' => '021-' . $this->faker->numerify('5299####'),
            'portal_url' => 'https://portal.' . strtolower(str_replace(' ', '', $name)) . '.com',
            'address' => $this->faker->address(),
            'bank_name' => $this->faker->randomElement(['Bank Mandiri', 'BCA', 'BNI', 'BRI']),
            'bank_account_no' => $this->faker->numerify('12000########'),
            'bank_account_name' => "PT {$name} INDONESIA",
            'is_active' => true,
        ];
    }
}
