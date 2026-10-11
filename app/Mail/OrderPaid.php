<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Al cliente: pago confirmado, con el detalle y el link de seguimiento. */
class OrderPaid extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Recibimos el pago de tu pedido :number', ['number' => $this->order->number()]));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.order-paid', with: ['admin' => false]);
    }
}
