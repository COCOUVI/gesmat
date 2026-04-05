<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

final class WorkflowActionMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    /**
     * @param  array<int, array{label: string, value: string}>  $details
     * @param  array<int, string>  $highlights
     * @param  array<int, array{label: string, url: string}>  $actionLinks
     */
    public function __construct(
        private readonly string $subjectLine,
        private readonly string $title,
        private readonly string $recipientName,
        private readonly string $intro,
        private readonly array $details = [],
        private readonly array $highlights = [],
        private readonly array $actionLinks = [],
        private readonly ?string $attachmentPath = null,
        private readonly ?string $attachmentName = null,
        private readonly ?string $footerNote = null,
    ) {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.workflow_action',
            with: [
                'title' => $this->title,
                'recipientName' => $this->recipientName,
                'intro' => $this->intro,
                'details' => $this->details,
                'highlights' => $this->highlights,
                'actionLinks' => $this->actionLinks,
                'footerNote' => $this->footerNote,
                'hasAttachment' => $this->attachmentPath !== null,
                'logoPath' => public_path('images/jaspe_logo_noir_web.png'),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if ($this->attachmentPath === null || ! Storage::disk('public')->exists($this->attachmentPath)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('public', $this->attachmentPath)
                ->as($this->attachmentName ?? basename($this->attachmentPath))
                ->withMime('application/pdf'),
        ];
    }
}
