<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class sellerPasswordResetEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $seller;
    public $reset_password;
    public $firstname;
    


    /**
     * Create a new message instance.
     */
    public function __construct($seller, $reset_password, $firstname)
    {
        $this->seller = $seller;
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

    public function content(): Content
    {
        return new Content(
            view: 'emails.sellerPasswordResetEmail',
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
