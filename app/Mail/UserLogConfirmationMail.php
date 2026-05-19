<?php

namespace App\Mail;

use App\Models\Logs;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserLogConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    // Declarar a variável pública para que fique automaticamente disponível na view do blade
    public $log;

    /**
     * Create a new message instance.
     */
    public function __construct(Logs $log)
    {
        $this->log = $log;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Timely - Pedido de Registo de Ponto Recebido',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.user_log_confirmation',
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