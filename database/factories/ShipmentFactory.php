<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Shipment;
use App\Models\ShippingVendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShipmentFactory extends Factory
{
    protected $model = Shipment::class;

    public function definition(): array
    {
        $yearMonth = now()->format('Ym');
        $randomSeq = $this->faker->unique()->numberBetween(1000, 9999);
        $eta = now()->addDays($this->faker->numberBetween(1, 10));

        return [
            'project_no' => "SHP-{$yearMonth}-{$randomSeq}",
            'client_id' => Client::factory(),
            'shipping_vendor_id' => ShippingVendor::factory(),
            'jenis_layanan' => $this->faker->randomElement(['IMPORT_BC20', 'EKSPORT_BC30']),
            'nomor_bl_awb' => 'BL-' . strtoupper($this->faker->bothify('????-########')),
            'tgl_bl_awb' => now()->subDays(5),
            'nama_kapal' => 'MV ' . strtoupper($this->faker->word()) . ' ' . $this->faker->numberBetween(100, 999),
            'voyage_no' => 'V.' . $this->faker->numerify('###N'),
            'pelabuhan_muat' => 'CNSHA (Shanghai Port)',
            'pelabuhan_bongkar' => 'IDTPP (Tanjung Priok)',
            'eta_date' => $eta,
            'total_kontainer' => 1,
            'nomor_kontainer' => strtoupper($this->faker->bothify('????-#######')) . ' (1x40HC)',
            'total_kemasan' => $this->faker->numberBetween(50, 500),
            'jenis_kemasan' => 'CTN (Cartons)',
            'total_bruto' => $this->faker->randomFloat(2, 2000, 20000),
            'total_netto' => $this->faker->randomFloat(2, 1800, 18000),
            'kode_valuta' => 'USD',
            'total_nilai_valuta' => $this->faker->randomFloat(2, 10000, 100000),
            'ndbm_kurs' => 16200.00,
            'total_nilai_idr' => $this->faker->randomFloat(2, 150000000, 1500000000),
            'free_time_days' => 7,
            'free_time_expired_at' => (clone $eta)->addDays(7),
            'status_tahap' => 'DOC_COLLECTING',
            'status_do' => 'PENDING_REQUEST',
        ];
    }
}
