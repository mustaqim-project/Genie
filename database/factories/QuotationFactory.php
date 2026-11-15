<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    public function definition(): array
    {
        $yearMonth = now()->format('Ym');
        $randomSeq = $this->faker->unique()->numberBetween(1000, 9999);
        $subtotal = 19500000.00;
        $ppn = 165000.00;

        return [
            'quotation_no' => "QUO-{$yearMonth}-{$randomSeq}",
            'client_id' => Client::factory(),
            'tanggal' => now(),
            'valid_until' => now()->addDays(30),
            'pelabuhan_asal' => 'CNSHA (Shanghai Port, China)',
            'pelabuhan_tujuan' => 'IDTPP (Tanjung Priok, Jakarta)',
            'term_of_shipment' => 'FOB / CY-CY 1x40HC',
            'rincian_biaya' => [
                ['komponen' => 'Ocean Freight', 'biaya' => 12500000],
                ['komponen' => 'THC Pelabuhan', 'biaya' => 2150000],
                ['komponen' => 'Penebusan DO Pelayaran', 'biaya' => 850000],
                ['komponen' => 'Jasa PPJK (PIB)', 'biaya' => 1500000],
                ['komponen' => 'Trucking Delivery', 'biaya' => 2500000],
            ],
            'subtotal_idr' => $subtotal,
            'ppn_idr' => $ppn,
            'total_idr' => $subtotal + $ppn,
            'status' => 'DRAFT',
            'discount_approval_status' => 'NOT_REQUIRED',
            'syarat_ketentuan' => 'Tarif resmi all-in exclude official customs taxes.',
        ];
    }
}
