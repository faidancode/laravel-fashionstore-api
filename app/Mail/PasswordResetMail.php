<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public string $name,
        public string $token,
        public \Illuminate\Support\Carbon $expiresAt
    ) {}

    public function build(): self
    {
        return $this->subject('Reset Password')
            ->view('emails.auth.reset-password');
    }
}
