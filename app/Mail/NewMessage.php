<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Message $msg) {}

    public function envelope(): Envelope
    {
        $type = Message::TYPES[$this->msg->type] ?? $this->msg->type;

        return new Envelope(
            subject: '[Web'.($this->msg->locale === 'en' ? ' EN' : '')."] {$type} #{$this->msg->id}: {$this->msg->subject}",
            replyTo: $this->msg->email ? [new Address($this->msg->email, $this->msg->name)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.new-message');
    }
}
