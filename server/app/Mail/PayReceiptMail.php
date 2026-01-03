<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $transaction, public $subscription)
    {
        //
    }

    /**
     * Get the message envelope.
     * this is for the email metadata like subject from
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: 'ibukunagbaoye@gmail.com', // sender email
            subject: "Payment receipt for {$this->transaction->tx_ref}",
        );
    }

    /**
     * Get the message content definition.
     * this is defines the blade template that will be used
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment_receipt',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     * if there is any file to be attach to the email
     */
    public function attachments(): array
    {
        return [];
    }
}
