<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WithdrawalOTP extends Mailable
{
    use Queueable, SerializesModels;
    public $amount;
    public $otp;
    public $firstname;
    public $withdrawalId;
    


    /**
     * Create a new message instance.
     */
    public function __construct($otp, $firstname,$amount,$withdrawalId)
    {
       // $this->seller = $seller;
        $this->otp = $otp;
        $this->firstname = $firstname;
        $this->amount = $amount;
        $this->withdrawalId = $withdrawalId;
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Withdrawal O T P',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.withdrawalOTP',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
