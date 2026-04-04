<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class IdentifiantsEnvoyes extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $password,
    ) {
        $this->afterCommit();
    }

    public function build()
    {
        return $this->subject('Vos identifiants de connexion')
            ->view('emails.identifiants')
            ->with([
                'loginUrl' => route('login'),
                'logoPath' => public_path('images/jaspe_logo_noir_web.png'),
            ]);
    }
}
