<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Markdown;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

use Illuminate\Contracts\Queue\ShouldQueue;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;


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


   /* public function build()
    {
        return $this->subject('Order Confirmation')
                    ->view('emails.orderConfirmationEmail')
                    ->with('orders', $this->orders);
    }*/

    public function build()
    {
        // Load the view content
        $htmlContent = view('emails.orderConfirmationEmail', ['orders' => $this->orders])->render();

        // Load the CSS
        $cssPath1 = public_path('css/product-confirmed.css');
        $cssPath2 = public_path('css/bootstrap.min.css');

        $cssContent1 = file_get_contents($cssPath1);
        $cssContent2 = file_get_contents($cssPath2);

        // Combine CSS files
        $combinedCss = $cssContent1 . "\n" . $cssContent2;

        // Inline the CSS
        $cssToInlineStyles = new CssToInlineStyles();
        $htmlWithInlineCss = $cssToInlineStyles->convert(
            $htmlContent,
            $combinedCss
        );

        return $this->subject('Order Confirmation')
                    ->html($htmlWithInlineCss);
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