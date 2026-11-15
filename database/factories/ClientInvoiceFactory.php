<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientInvoice;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientInvoiceFactory extends Factory
{
    protected $model = ClientInvoice::class;

    public function definition(): array
    {
        $yearMonth = now()->format('Ym');
        $randomSeq = $this->faker->unique()->numberBetween(1000, 9999);
        $jasaPpjk = 1500000.00;
        $ppn = $jasaPpjk * 0.11;
        $pelayaran = 3850000.00;
        $trucking = 2500000.00;
        $talangan = 18500000.00;
        $subtotal = $talangan + $pelayaran + $jasaPpjk + $trucking;
        $total = $subtotal + $ppn;

        return [
            'invoice_no' => "INV-{$yearMonth}-{$randomSeq}",
            'shipment_id' => Shipment::factory(),
            'client_id' => Client::factory(),
            'tanggal' => now(),
            'jatuh_tempo' => now()->addDays(14),
            'talangan_pabean_idr' => $talangan,
            'biaya_pelayaran_idr' => $pelayaran,
            'jasa_ppjk_idr' => $jasaPpjk,
            'biaya_trucking_idr' => $trucking,
            'subtotal_idr' => $subtotal,
            'ppn_jasa_idr' => $ppn,
            'total_tagihan_idr' => $total,
            'status' => 'UNPAID',
            'catatan_pembayaran' => 'Transfer via Mandiri 120-00-1122334-5',
        ];
    }
}
