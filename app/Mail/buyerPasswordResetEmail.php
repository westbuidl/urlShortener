<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class buyerPasswordResetEmail extends Mailable
{
   // use Queueable, SerializesModels;
    use Queueable, SerializesModels;
    public $buyer;
    public $reset_password;
    public $firstname;
    


    /**
     * Create a new message instance.
     */
    public function __construct($buyer, $reset_password, $firstname)
    {
        $this->buyer = $buyer;
        $this->reset_password = $reset_password;
        $this->firstname = $firstname;
        //
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
       

        return new Envelope(
            subject: $this->firstname . ' '  .' Your Password Reset Code' 
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.buyerPasswordReset',
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
