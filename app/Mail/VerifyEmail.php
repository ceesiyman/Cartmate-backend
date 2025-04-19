<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $verificationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otp, string $verificationUrl)
    {
        $this->otp = $otp;
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->markdown('emails.verify-email')
                    ->subject('Verify Your Email Address');
    }
} 