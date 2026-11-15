<?php

namespace Database\Factories;

use App\Models\Shipment;
use App\Models\VendorBill;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorBillFactory extends Factory
{
    protected $model = VendorBill::class;

    public function definition(): array
    {
        return [
            'vendor_type' => $this->faker->randomElement(['SHIPPING_LINE', 'TRUCKING', 'DEPO_CONTAINER', 'TPS']),
            'shipment_id' => Shipment::factory(),
            'ref_no' => 'BILL-' . strtoupper($this->faker->bothify('???-#####')),
            'amount' => $this->faker->randomFloat(2, 500000, 15000000),
            'due_date' => now()->addDays(7),
            'status' => 'DRAFT',
            'approved_by_id' => null,
        ];
    }
}
