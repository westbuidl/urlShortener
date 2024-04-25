<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class companyBuyerPasswordResetEmail extends Mailable
{
    // use Queueable, SerializesModels;
     use Queueable, SerializesModels;
     public $companyBuyer;
     public $reset_password;
     public $companyname;
     
 
 
     /**
      * Create a new message instance.
      */
     public function __construct($companyBuyer, $reset_password, $companyname)
     {
         $this->companyBuyer = $companyBuyer;
         $this->reset_password = $reset_password;
         $this->companyname = $companyname;
         //
     }
     /**
      * Get the message envelope.
      */
     public function envelope(): Envelope
     {
        
 
         return new Envelope(
             subject: $this->companyname . ' '  .' Your Password Reset Code' 
         );
     }
 
     /**
      * Get the message content definition.
      */
     public function content(): Content
     {
         return new Content(
             view: 'emails.companyBuyerPasswordResetEmail',
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
