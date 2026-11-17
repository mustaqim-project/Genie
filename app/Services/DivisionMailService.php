<?php

namespace App\Services;

use App\Models\ClientInvoice;
use App\Models\DocumentTemplate;
use App\Models\Quotation;
use App\Models\Shipment;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DivisionMailService
{
    /**
     * Dapatkan konfigurasi pengirim email berdasarkan divisi
     */
    public static function getDivisionConfig(string $division): array
    {
        $division = strtolower($division);
        $base = match ($division) {
            'marketing' => [
                'division_name' => 'Marketing & Sales',
                'from_address' => SystemSetting::get('email_marketing_from_address', 'marketing@genie-logistics.id'),
                'from_name' => SystemSetting::get('email_marketing_from_name', 'GENIE Marketing & Sales Dept'),
                'reply_to' => SystemSetting::get('email_marketing_reply_to', 'marketing@genie-logistics.id'),
                'default_subject' => SystemSetting::get('email_marketing_subject', 'Penawaran Harga Resmi Ekspor Impor - PT GENIE Logistik Indonesia'),
            ],
            'finance' => [
                'division_name' => 'Finance & Penagihan',
                'from_address' => SystemSetting::get('email_finance_from_address', 'finance@genie-logistics.id'),
                'from_name' => SystemSetting::get('email_finance_from_name', 'GENIE Finance & Penagihan'),
                'reply_to' => SystemSetting::get('email_finance_reply_to', 'finance@genie-logistics.id'),
                'default_subject' => SystemSetting::get('email_finance_subject', 'Pemberitahuan Tagihan Resmi (Invoice P19) - PT GENIE Logistik Indonesia'),
            ],
            'document' => [
                'division_name' => 'Dokumen Kepabeanan',
                'from_address' => SystemSetting::get('email_document_from_address', 'dokumen@genie-logistics.id'),
                'from_name' => SystemSetting::get('email_document_from_name', 'GENIE Customs & Documentation'),
                'reply_to' => SystemSetting::get('email_document_reply_to', 'dokumen@genie-logistics.id'),
                'default_subject' => SystemSetting::get('email_document_subject', 'Konfirmasi & Approval Draft Dokumen Pabean (PIB/PEB)'),
            ],
            'traffic' => [
                'division_name' => 'Traffic & Lapangan',
                'from_address' => SystemSetting::get('email_traffic_from_address', 'traffic@genie-logistics.id'),
                'from_name' => SystemSetting::get('email_traffic_from_name', 'GENIE Traffic & Fleet Dispatch'),
                'reply_to' => SystemSetting::get('email_traffic_reply_to', 'traffic@genie-logistics.id'),
                'default_subject' => SystemSetting::get('email_traffic_subject', 'Update Status Kargo, Estimasi Kedatangan (ETA) & SPPB'),
            ],
            default => [
                'division_name' => 'General Operations',
                'from_address' => config('mail.from.address', 'noreply@genie-logistics.id'),
                'from_name' => config('mail.from.name', 'PT GENIE Logistik Indonesia'),
                'reply_to' => config('mail.from.address', 'noreply@genie-logistics.id'),
                'default_subject' => 'Pemberitahuan Layanan PT GENIE Logistik Indonesia',
            ]
        };

        $base['is_custom_smtp'] = (bool) SystemSetting::get("email_{$division}_smtp_custom", false);
        $base['smtp_host'] = SystemSetting::get("email_{$division}_smtp_host", '');
        $base['smtp_port'] = (int) SystemSetting::get("email_{$division}_smtp_port", 587);
        $base['smtp_encryption'] = SystemSetting::get("email_{$division}_smtp_encryption", 'tls');
        $base['smtp_username'] = SystemSetting::get("email_{$division}_smtp_username", '');
        $base['smtp_password'] = SystemSetting::get("email_{$division}_smtp_password", '');

        return $base;
    }

    /**
     * Dapatkan instance mailer dinamis per divisi
     */
    public static function getMailer(string $division, ?array $runtimeConfig = null)
    {
        $division = strtolower($division);
        $cfg = self::getDivisionConfig($division);
        if ($runtimeConfig) {
            $cfg = array_merge($cfg, array_filter($runtimeConfig, fn($v) => !is_null($v) && $v !== ''));
            if (isset($runtimeConfig['is_custom_smtp'])) {
                $cfg['is_custom_smtp'] = (bool) $runtimeConfig['is_custom_smtp'];
            }
        }

        if (!empty($cfg['is_custom_smtp']) && !empty($cfg['smtp_host']) && !empty($cfg['smtp_username'])) {
            $encryption = $cfg['smtp_encryption'] ?? 'tls';
            if ($encryption === 'none' || $encryption === 'null' || empty($encryption)) {
                $encryption = null;
            }

            $mailerName = "smtp_{$division}_" . uniqid();
            config([
                "mail.mailers.{$mailerName}" => [
                    'transport' => 'smtp',
                    'host' => $cfg['smtp_host'],
                    'port' => (int) ($cfg['smtp_port'] ?: 587),
                    'encryption' => $encryption,
                    'username' => $cfg['smtp_username'],
                    'password' => $cfg['smtp_password'] ?: '',
                    'timeout' => 10,
                    'local_domain' => env('MAIL_EHLO_DOMAIN'),
                ]
            ]);

            return Mail::mailer($mailerName);
        }

        return Mail::mailer(config('mail.default', 'smtp'));
    }

    /**
     * Uji coba koneksi SMTP per divisi
     */
    public static function testConnection(string $division, ?string $testRecipient = null, ?array $runtimeConfig = null): array
    {
        try {
            $division = strtolower($division);
            $cfg = self::getDivisionConfig($division);
            if ($runtimeConfig) {
                $cfg = array_merge($cfg, array_filter($runtimeConfig, fn($v) => !is_null($v) && $v !== ''));
            }

            $recipient = $testRecipient ?: $cfg['from_address'];

            if (empty($recipient)) {
                return ['success' => false, 'message' => 'Alamat email tujuan uji coba tidak boleh kosong.'];
            }

            $mailer = self::getMailer($division, $runtimeConfig);
            $timeNow = now()->format('d/m/Y H:i:s');
            $customInfo = !empty($cfg['smtp_host'])
                ? "Dedicated Mail Server: {$cfg['smtp_host']}:{$cfg['smtp_port']} ({$cfg['smtp_encryption']}) | User: {$cfg['smtp_username']}"
                : "Default Server (.env: " . config('mail.mailers.smtp.host', 'default') . ")";

            $mailer->raw("Halo Tim Logistik GENIE,\n\nEmail uji coba koneksi SMTP untuk Divisi: {$cfg['division_name']}.\n\n✓ Status: KONEKSI DEDICATED SMTP BERHASIL\n✓ Waktu Uji Coba: {$timeNow} WIB\n✓ Alamat Pengirim: {$cfg['from_name']} <{$cfg['from_address']}>\n✓ Server Konfigurasi: {$customInfo}\n\nPengaturan 4 Akun Email dan 4 SMTP Terpisah telah aktif dan siap digunakan untuk pengiriman dokumen operasional otomatis.", function ($message) use ($recipient, $cfg) {
                $message->to($recipient)
                    ->from($cfg['from_address'], $cfg['from_name'])
                    ->replyTo($cfg['reply_to'])
                    ->subject("✓ Uji Coba Sukses: Dedicated SMTP Divisi {$cfg['division_name']}");
            });

            Log::info("Tes koneksi SMTP Divisi {$division} sukses terkirim ke {$recipient}");
            return [
                'success' => true,
                'message' => "Koneksi Dedicated SMTP Divisi {$cfg['division_name']} BERHASIL! Email uji coba berhasil dikirim ke {$recipient}."
            ];
        } catch (\Throwable $e) {
            Log::error("Tes koneksi SMTP Divisi {$division} gagal: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Gagal terhubung ke server SMTP: " . $e->getMessage()
            ];
        }
    }

    /**
     * Kirim Email Penawaran Harga (Divisi Marketing)
     */
    public function sendQuotation(Quotation $quotation, ?string $recipientEmail = null, ?string $customNotes = null): array
    {
        $to = $recipientEmail ?: $quotation->client?->email;
        if (empty($to)) {
            return ['success' => false, 'message' => 'Alamat email klien tidak ditemukan. Silakan lengkapi profil klien atau ketik email tujuan.'];
        }

        $cfg = self::getDivisionConfig('marketing');
        $tpl = DocumentTemplate::getTemplate('EMAIL_MARKETING_DEFAULT', 'marketing');
        $rendered = $tpl ? $tpl->render([
            'client_name' => $quotation->client?->name ?? 'Klien',
            'contact_person' => $quotation->client?->pic_name ?? 'Pimpinan',
            'quotation_no' => $quotation->quotation_no ?: 'QUO-' . $quotation->id,
            'origin_port' => $quotation->origin_port ?? 'Port of Loading',
            'destination_port' => $quotation->destination_port ?? 'Port of Discharge',
            'total_amount' => 'Rp ' . number_format($quotation->total_estimate ?? $quotation->total_idr ?? 0, 0, ',', '.'),
            'valid_until' => $quotation->valid_until ? $quotation->valid_until->format('d/m/Y') : now()->addDays(30)->format('d/m/Y'),
            'custom_notes' => $customNotes ? "Catatan Khusus: {$customNotes}" : '',
        ]) : null;

        $subject = ($rendered && !empty($rendered['subject'])) ? $rendered['subject'] : "{$cfg['default_subject']} - " . ($quotation->quotation_no ?: 'QUO-' . $quotation->id);
        $customBody = $rendered['body'] ?? null;

        try {
            $pdf = Pdf::loadView('pdf.quotation', compact('quotation'))->setPaper('a4');
            $pdfContent = $pdf->output();

            self::getMailer('marketing')->send('emails.marketing_quotation', ['quotation' => $quotation, 'customNotes' => $customNotes, 'customBody' => $customBody], function ($message) use ($to, $cfg, $subject, $pdfContent, $quotation) {
                $message->to($to)
                    ->from($cfg['from_address'], $cfg['from_name'])
                    ->replyTo($cfg['reply_to'])
                    ->subject($subject)
                    ->attachData($pdfContent, "Quotation_{$quotation->quotation_no}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
            });

            if ($quotation->status === 'DRAFT') {
                $quotation->update(['status' => 'SENT']);
            }

            Log::info("Email Penawaran dikirim ke {$to} dari Divisi Marketing ({$cfg['from_address']})");
            return [
                'success' => true,
                'from' => $cfg['from_address'],
                'division' => $cfg['division_name'],
                'recipient' => $to,
                'message' => "Email Penawaran Harga berhasil dikirim ke {$to} menggunakan akun resmi Divisi Marketing ({$cfg['from_address']})!"
            ];
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim email penawaran: " . $e->getMessage());
            return ['success' => false, 'message' => "Gagal mengirim email: " . $e->getMessage()];
        }
    }

    /**
     * Kirim Email Tagihan Invoice P19 (Divisi Finance)
     */
    public function sendInvoice(ClientInvoice $invoice, ?string $recipientEmail = null, ?string $customNotes = null): array
    {
        $to = $recipientEmail ?: $invoice->client?->email;
        if (empty($to)) {
            return ['success' => false, 'message' => 'Alamat email klien tidak ditemukan. Silakan lengkapi profil klien atau ketik email tujuan.'];
        }

        $cfg = self::getDivisionConfig('finance');
        $tpl = DocumentTemplate::getTemplate('EMAIL_FINANCE_DEFAULT', 'finance');
        $rendered = $tpl ? $tpl->render([
            'client_name' => $invoice->client?->name ?? 'Klien',
            'invoice_no' => $invoice->invoice_number ?: 'INV-' . $invoice->id,
            'project_no' => $invoice->shipment?->project_no ?? '-',
            'bl_number' => $invoice->shipment?->bl_number ?? '-',
            'total_amount' => 'Rp ' . number_format($invoice->total_tagihan ?? $invoice->total_amount ?? 0, 0, ',', '.'),
            'due_date' => $invoice->jatuh_tempo ? $invoice->jatuh_tempo->format('d/m/Y') : now()->addDays(14)->format('d/m/Y'),
            'custom_notes' => $customNotes ? "Catatan Tagihan: {$customNotes}" : '',
            'bank_info' => SystemSetting::get('email_finance_bank_info', 'Bank Mandiri: 141-00-9876543-2 a/n PT GENIE LOGISTIK INDONESIA'),
        ]) : null;

        $subject = ($rendered && !empty($rendered['subject'])) ? $rendered['subject'] : "{$cfg['default_subject']} - " . ($invoice->invoice_number ?: 'INV-' . $invoice->id);
        $customBody = $rendered['body'] ?? null;

        try {
            $pdf = Pdf::loadView('pdf.invoice', compact('invoice'))->setPaper('a4');
            $pdfContent = $pdf->output();

            self::getMailer('finance')->send('emails.finance_invoice', ['invoice' => $invoice, 'customNotes' => $customNotes, 'customBody' => $customBody], function ($message) use ($to, $cfg, $subject, $pdfContent, $invoice) {
                $message->to($to)
                    ->from($cfg['from_address'], $cfg['from_name'])
                    ->replyTo($cfg['reply_to'])
                    ->subject($subject)
                    ->attachData($pdfContent, "Invoice_{$invoice->invoice_number}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
            });

            Log::info("Email Invoice dikirim ke {$to} dari Divisi Finance ({$cfg['from_address']})");
            return [
                'success' => true,
                'from' => $cfg['from_address'],
                'division' => $cfg['division_name'],
                'recipient' => $to,
                'message' => "Email Tagihan Invoice P19 berhasil dikirim ke {$to} menggunakan akun resmi Divisi Finance ({$cfg['from_address']})!"
            ];
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim email invoice: " . $e->getMessage());
            return ['success' => false, 'message' => "Gagal mengirim email: " . $e->getMessage()];
        }
    }

    /**
     * Kirim Email Review Draft Pabean PIB/PEB (Divisi Dokumen)
     */
    public function sendDocumentDraft(Shipment $shipment, ?string $recipientEmail = null, ?string $customNotes = null): array
    {
        $to = $recipientEmail ?: $shipment->client?->email;
        if (empty($to)) {
            return ['success' => false, 'message' => 'Alamat email klien tidak ditemukan. Silakan lengkapi profil klien atau ketik email tujuan.'];
        }

        $cfg = self::getDivisionConfig('document');
        $tpl = DocumentTemplate::getTemplate('EMAIL_DOCUMENT_DEFAULT', 'document');
        $rendered = $tpl ? $tpl->render([
            'client_name' => $shipment->client?->name ?? 'Klien',
            'nomor_aju' => $shipment->nomor_aju ?: $shipment->project_no,
            'project_no' => $shipment->project_no,
            'document_type' => $shipment->type === 'EXPORT' ? 'BC 3.0 (PEB Ekspor)' : 'BC 2.0 (PIB Impor)',
            'vessel_name' => $shipment->vessel_name ?? 'Kapal Pengangkut',
            'voyage_no' => $shipment->voyage_number ?? '-',
            'pol' => $shipment->port_of_loading ?? '-',
            'pod' => $shipment->port_of_discharge ?? '-',
            'custom_notes' => $customNotes ? "Catatan Dokumen: {$customNotes}" : '',
        ]) : null;

        $subject = ($rendered && !empty($rendered['subject'])) ? $rendered['subject'] : "{$cfg['default_subject']} - Aju: " . ($shipment->nomor_aju ?: $shipment->project_no);
        $customBody = $rendered['body'] ?? null;

        try {
            $pdfView = $shipment->type === 'EXPORT' ? 'pdf.peb' : 'pdf.pib';
            $pdf = Pdf::loadView($pdfView, compact('shipment'))->setPaper('a4');
            $pdfContent = $pdf->output();

            self::getMailer('document')->send('emails.document_pabean_draft', ['shipment' => $shipment, 'customNotes' => $customNotes, 'customBody' => $customBody], function ($message) use ($to, $cfg, $subject, $pdfContent, $shipment) {
                $docName = $shipment->type === 'EXPORT' ? 'Draft_PEB' : 'Draft_PIB';
                $message->to($to)
                    ->from($cfg['from_address'], $cfg['from_name'])
                    ->replyTo($cfg['reply_to'])
                    ->subject($subject)
                    ->attachData($pdfContent, "{$docName}_{$shipment->project_no}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
            });

            Log::info("Email Draft Pabean dikirim ke {$to} dari Divisi Dokumen ({$cfg['from_address']})");
            return [
                'success' => true,
                'from' => $cfg['from_address'],
                'division' => $cfg['division_name'],
                'recipient' => $to,
                'message' => "Email Draft Dokumen Pabean berhasil dikirim ke {$to} menggunakan akun resmi Divisi Dokumen ({$cfg['from_address']})!"
            ];
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim email dokumen: " . $e->getMessage());
            return ['success' => false, 'message' => "Gagal mengirim email: " . $e->getMessage()];
        }
    }

    /**
     * Kirim Email Update Status Kargo & ETA (Divisi Traffic)
     */
    public function sendTrafficEta(Shipment $shipment, ?string $recipientEmail = null, ?string $customNotes = null, string $suratJalanTemplate = 'fcl'): array
    {
        $to = $recipientEmail ?: $shipment->client?->email;
        if (empty($to)) {
            return ['success' => false, 'message' => 'Alamat email klien tidak ditemukan. Silakan lengkapi profil klien atau ketik email tujuan.'];
        }

        $cfg = self::getDivisionConfig('traffic');
        $tpl = DocumentTemplate::getTemplate('EMAIL_TRAFFIC_DEFAULT', 'traffic');
        $rendered = $tpl ? $tpl->render([
            'client_name' => $shipment->client?->name ?? 'Klien',
            'project_no' => $shipment->project_no,
            'vessel_name' => $shipment->vessel_name ?? 'Sarana Pengangkut',
            'voyage_no' => $shipment->voyage_number ?? '-',
            'eta_date' => $shipment->eta_date ? $shipment->eta_date->format('d/m/Y H:i') . ' WIB' : 'Menunggu Konfirmasi',
            'shipment_status' => $shipment->status_tahap ?? $shipment->status ?? 'Dalam Perjalanan',
            'tps_location' => $shipment->tps_location ?? 'Pelabuhan Tujuan',
            'custom_notes' => $customNotes ? "Catatan Lapangan: {$customNotes}" : '',
        ]) : null;

        $subject = ($rendered && !empty($rendered['subject'])) ? $rendered['subject'] : "{$cfg['default_subject']} - {$shipment->project_no} ({$shipment->vessel_name})";
        $customBody = $rendered['body'] ?? null;

        try {
            $viewMap = [
                'fcl' => 'pdf.surat_jalan_fcl',
                'lcl' => 'pdf.surat_jalan_lcl',
                'inter_factory' => 'pdf.surat_jalan_inter_factory',
                'empty_depo' => 'pdf.surat_jalan_empty_depo',
            ];
            $sjView = $viewMap[$suratJalanTemplate] ?? 'pdf.surat_jalan_fcl';
            $pdf = Pdf::loadView($sjView, compact('shipment'))->setPaper('a4');
            $pdfContent = $pdf->output();

            self::getMailer('traffic')->send('emails.traffic_eta_notice', ['shipment' => $shipment, 'customNotes' => $customNotes, 'customBody' => $customBody], function ($message) use ($to, $cfg, $subject, $pdfContent, $shipment) {
                $message->to($to)
                    ->from($cfg['from_address'], $cfg['from_name'])
                    ->replyTo($cfg['reply_to'])
                    ->subject($subject)
                    ->attachData($pdfContent, "Surat_Jalan_{$shipment->project_no}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
            });

            Log::info("Email ETA Traffic dikirim ke {$to} dari Divisi Traffic ({$cfg['from_address']})");
            return [
                'success' => true,
                'from' => $cfg['from_address'],
                'division' => $cfg['division_name'],
                'recipient' => $to,
                'message' => "Email Status Kargo & ETA berhasil dikirim ke {$to} menggunakan akun resmi Divisi Traffic ({$cfg['from_address']})!"
            ];
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim email traffic: " . $e->getMessage());
            return ['success' => false, 'message' => "Gagal mengirim email: " . $e->getMessage()];
        }
    }
}