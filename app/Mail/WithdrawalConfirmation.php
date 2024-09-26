<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WithdrawalConfirmation extends Mailable
{
    use Queueable, SerializesModels;

   // public $otp;
    public $firstname;
    public $amount;
    public $withdrawalId;
    //public $initiated_at;
    


    /**
     * Create a new message instance.
     */
    public function __construct($firstname, $amount, $withdrawalId)
    {
       // $this->seller = $seller;
        //$this->otp = $otp;
        $this->firstname = $firstname;
        $this->amount = $amount;
        $this->withdrawalId = $withdrawalId;
        //$this->initiated_at = $initiated_at;
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Withdrawal Submitted',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.WithdrawalConfirmation',
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
