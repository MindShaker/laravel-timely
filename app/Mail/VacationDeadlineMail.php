<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VacationDeadlineMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public int    $vacationCount,
        public int    $allowance,
        public string $deadlineDate,
        public string $calendarUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Lembrete: planeia as tuas férias antes de 31 de Março');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.vacation-deadline');
    }
}
