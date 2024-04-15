<?php

namespace App\Mail;

use view;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class buyerSignupEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $buyer;
    public $firstname;
    

    /**
     * Create a new message instance.
     */
    public function __construct($buyer, $firstname)
    {
        $this->buyer = $buyer;
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
            view:'emails.buyerSignupEmail',
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
