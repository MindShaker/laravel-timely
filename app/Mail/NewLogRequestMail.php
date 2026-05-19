<?php

namespace App\Mail;

use App\Models\Logs;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewLogRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    // 1. Declarar as propriedades públicas na classe
    public $log;
    public $solicitante;
    public $approveUrl;
    public $rejectUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Logs $log, User $solicitante, $approveUrl, $rejectUrl)
    {
        $this->log = $log;
        $this->solicitante = $solicitante;
        $this->approveUrl = $approveUrl;
        $this->rejectUrl = $rejectUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Timely - Novo Pedido de Inserção de Ponto (' . $this->solicitante->name . ')',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.new_log_request', // Garanti o caminho para a tua pasta 'mails'
            with: [
                'approveUrl' => $this->approveUrl,
                'rejectUrl' => $this->rejectUrl,
            ]
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