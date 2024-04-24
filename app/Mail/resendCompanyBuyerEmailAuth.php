<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class resendCompanyBuyerEmailAuth extends Mailable
{
    use Queueable, SerializesModels;

    public $seller;
    public $firstname;
    

    /**
     * Create a new message instance.
     */
    public function __construct($seller, $firstname)
    {
        $this->seller = $seller;
        $this->firstname = $firstname;
        //$this->business = $business;
        //
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->firstname . '  '  .'verify your email' 
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.resendCompanyBuyerEmailAuth',
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

