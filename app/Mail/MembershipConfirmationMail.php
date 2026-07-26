<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MembershipConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $fullName,
    ) {}

    public function build()
    {
        return $this->subject('Membership Application Received')
            ->view('emails.membership.confirmation')
            ->with([
                'fullName' => $this->fullName,
            ]);
    }
}
