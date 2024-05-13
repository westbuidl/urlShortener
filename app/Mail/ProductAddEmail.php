<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class ProductAddEmail extends Mailable
{
    
    use Queueable, SerializesModels;
    public $user;
    public $product;
    public $firstname;
    public $product_name;
    public $quantityin_stock;
    public $productId;
    /**
     * Create a new message instance.
     */
    public function __construct($user, $product, $firstname,$product_name,$quantityin_stock,$productId)
    {
        $this->user = $user;
        $this->product = $product;
        $this->firstname = $firstname;
        $this->product_name = $product_name;
        $this->quantityin_stock = $quantityin_stock;
        $this->productId = $productId;
        //
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->quantityin_stock . ' ' .$this->product_name. ' '  .'added to your inventory' 
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view:'emails.productaddemail',
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
