<?php

namespace App\Mail;

use App\Models\Logs;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewLogStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $log;
    public $status;

    /**
     * Create a new message instance.
     */
    public function __construct(Logs $log, $status)
    {
        $this->log = $log;
        $this->status = $status;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusTexto = $this->status === 'approved' ? 'APROVADO' : 'RECUSADO';

        return new Envelope(
            subject: 'Timely - O teu pedido de ponto foi ' . $statusTexto . '!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.new_log_status', // Nome da vista ajustado também
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}