<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class HelpRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $senderEmail,
        private readonly string $body,
    ) {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Demande d'aide d'un employé",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aide',
            with: [
                'email' => $this->senderEmail,
                'body' => $this->body,
            ],
        );
    }
}
