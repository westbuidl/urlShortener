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

    public $companybuyer;
   // public $reset_password;
    public $companyname;
    

    /**
     * Create a new message instance.
     */
    public function __construct($companybuyer, $companyname)
    {
        $this->companybuyer = $companybuyer;
        $this->companyname = $companyname;
        //$this->business = $business;
        //
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->companyname .' '. 'verify your email to activate your account your Agroease account.'  
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

