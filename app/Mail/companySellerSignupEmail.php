<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class companySellerSignupEmail extends Mailable
{
    use Queueable, SerializesModels;


    public $companySeller;
    public $companyname;
    public $companyregnumber;
    /**
     * Create a new message instance.
     */
    public function __construct($companySeller, $companyname, $companyregnumber)
    {
        $this->companySeller = $companySeller;
        $this->companyname = $companyname;
        $this->companyregnumber = $companyregnumber;
        //
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Company Seller Signup Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.companySellerSignupEmail',
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
