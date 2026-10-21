<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Aviso al usuario cada vez que alguien entra al panel con su cuenta. */
class AdminLogin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public ?string $ip, public string $userAgent, public bool $usedRecoveryCode = false) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[Web] Ingreso al panel de administración');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.admin-login');
    }
}
