<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** A TNL: venta pagada, para preparar el envío. */
class OrderPaidAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Web] Venta pagada {$this->order->number()} — ".money($this->order->total),
            replyTo: [new Address($this->order->email, $this->order->customer_name)],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.order-paid', with: ['admin' => true]);
    }
}
