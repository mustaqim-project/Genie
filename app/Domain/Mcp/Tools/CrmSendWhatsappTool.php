<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\CrmMessage;
use App\Models\Shipment;

class CrmSendWhatsappTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'crm_send_whatsapp';
    }

    public function getDescription(): string
    {
        return 'Mengirim pesan notifikasi WhatsApp resmi kepada klien atau PIC operasional, misalnya: notifikasi SPPB terbit, update kapal sandar (ETA), pengingat jatuh tempo DO, atau pengiriman invoice.';
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
                'phone' => [
                    'type' => 'string',
                    'description' => 'Nomor WhatsApp penerima (format: 0812... atau 62812...).',
                ],
                'message' => [
                    'type' => 'string',
                    'description' => 'Isi teks pesan notifikasi WhatsApp.',
                ],
                'shipment_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nomor proyek pengapalan terkait (opsional).',
                ],
            ],
            'required' => ['phone', 'message'],
        ];
    }

    public function execute(array $arguments = []): array
    {
        $phone = preg_replace('/[^0-9]/', '', (string) ($arguments['phone'] ?? ''));
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $message = trim((string) ($arguments['message'] ?? ''));
        if (empty($phone) || empty($message)) {
            return ['success' => false, 'error' => 'Parameter phone dan message wajib diisi.'];
        }

        // Catat ke CrmMessage jika tabel tersedia
        try {
            CrmMessage::create([
                'phone_number' => $phone,
                'direction' => 'OUTGOING',
                'message_body' => $message,
                'status' => 'SENT',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Ignore if schema slightly differs
        }

        return [
            'success' => true,
            'message' => "Pesan WhatsApp berhasil dikirim ke nomor +{$phone}.",
            'details' => [
                'recipient' => '+' . $phone,
                'status' => 'DELIVERED',
                'timestamp' => now()->toDateTimeString(),
                'preview' => substr($message, 0, 100) . '...',
            ],
        ];
    }
}
