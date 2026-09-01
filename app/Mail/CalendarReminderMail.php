<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CalendarReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public array $unfilledDays,
        public string $calendarUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Lembrete: preenche o teu calendário de planeamento');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.calendar-reminder');
    }
}
