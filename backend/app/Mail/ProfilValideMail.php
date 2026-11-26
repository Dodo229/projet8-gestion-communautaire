<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

// L'e-mail envoyé à l'usager quand l'admin valide son profil.
class ProfilValideMail extends Mailable
{
    public function __construct(public string $nom)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre profil a été validé');
    }

    public function content(): Content
    {
        // Utilise le fichier resources/views/emails/profil-valide.blade.php
        return new Content(view: 'emails.profil-valide');
    }
}
