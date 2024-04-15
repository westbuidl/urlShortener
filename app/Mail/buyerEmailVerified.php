<?php

namespace App\Mail;

use view;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class buyerEmailVerified extends Mailable
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
            subject: 'Welcome to AgroEase'.'  '.$this->firstname . '  '  .'your email is verified.' 
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view:'emails.buyerEmailVerified',
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
