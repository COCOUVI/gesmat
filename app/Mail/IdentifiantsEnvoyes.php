<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class IdentifiantsEnvoyes extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $password;

    /**
     * Créer une nouvelle instance de mail.
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Construire le message.
     */
    public function build()
    {
        return $this->subject('Vos identifiants de connexion')
            ->view('emails.identifiants');
    }
}
