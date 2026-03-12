<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailConfirmationMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public string $name,
        public string $token,
        public string $pin,
        public \Illuminate\Support\Carbon $expiresAt
    ) {}

    public function build(): self
    {
        return $this->subject('Konfirmasi Email')
            ->view('emails.auth.confirm-email');
    }
}
