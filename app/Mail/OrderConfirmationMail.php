<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $orders;
   // public $productName;
    //public $orders;
    

    /**
     * Create a new message instance.
     */
    public function __construct($orders)
    {
        //
        //$this->order = $order;
        //$this->productName = $productName;
        $this->orders = $orders;
    }


    public function build()
    {
        return $this->subject('Order Confirmation')
                    ->view('emails.OrderConfirmationEmail')
                    ->with('orders', $this->orders);
    }
    /**
     * Get the message envelope.
     */
   /* public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->productName .' '.'Order Confirmation' 
        );
    }

    /**
     * Get the message content definition.
     *//*
    public function content(): Content
    {
        return new Content(
            view: 'emails.OrderConfirmationEmail',
        );

        
    }*/

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