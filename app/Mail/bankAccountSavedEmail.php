<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class bankAccountSavedEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $seller;
    public $name;

    /**
     * Create a new message instance.
     */
    public function __construct($seller, $name)
    {
        $this->seller = $seller;
        $this->name = $name;
        //$this->business = $business;
        //
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
       
        return new Envelope(
            subject: 'Hello'.'  '.$this->name . '  '.'your bank details is saved.' 
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view:'emails.bankAccountSavedEmail',
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
