<?php

namespace App\Mail;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorDoRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public Shipment $shipment;
    public ?string $customNotes;
    public string $emailSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(Shipment $shipment, ?string $customNotes = null)
    {
        $this->shipment = $shipment;
        $this->customNotes = $customNotes;
        $clientName = $shipment->client?->name ?? 'Klien';
        $this->emailSubject = "URGENT: Permintaan Invoice THC & DO Release - BL No: {$shipment->nomor_bl_awb} - {$clientName}";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor_do_request',
        );
    }
}
