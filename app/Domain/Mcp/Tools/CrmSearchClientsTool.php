<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Client;

class CrmSearchClientsTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'crm_search_clients';
    }

    public function getDescription(): string
    {
        return 'Mencari dan memfilter database klien importir/eksportir: kontak, NPWP, NIB, status keaktifan surat kuasa kepabeanan, nomor telepon PIC, dan total pengapalan aktif.';
    }

    public function getCategory(): string
    {
        return 'CRM & Klien';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Nama perusahaan, nomor NPWP, kode klien, atau nama PIC.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Jumlah maksimal data (default: 10).',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $q = trim((string) ($arguments['query'] ?? ''));
        $query = Client::withCount(['shipments'])->latest();

        if (!empty($q)) {
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('npwp', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%")
                  ->orWhere('pic_name', 'like', "%{$q}%");
            });
        }

        $limit = min((int) ($arguments['limit'] ?? 10), 30);
        $clients = $query->take($limit)->get();

        return [
            'success' => true,
            'total_found' => $clients->count(),
            'clients' => $clients->map(fn($c) => [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'npwp' => $c->npwp,
                'nib' => $c->nib,
                'pic' => $c->pic_name,
                'phone' => $c->phone,
                'email' => $c->email,
                'is_active' => (bool) $c->is_active,
                'surat_kuasa_valid' => $c->surat_kuasa_expired_at ? !$c->surat_kuasa_expired_at->isPast() : true,
                'total_shipments' => $c->shipments_count ?? 0,
            ])->toArray(),
        ];
    }
}
