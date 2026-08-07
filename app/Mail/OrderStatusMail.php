<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $customMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, ?string $customMessage = null)
    {
        $this->order = $order;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on Your Order ' . $this->order->order_number . ' – ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.order-status',
            text: 'emails.order-status-text',
            with: [
                'order' => $this->order,
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
                'customMessage' => $this->customMessage,
                'trackUrl' => $this->trackUrl(),
            ]
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

    /**
     * Build the public tracking URL for this order.
     */
    protected function trackUrl(): string
    {
        $base = rtrim(config('app.url'), '/');

        return $base . '/track-order/' . $this->order->id;
    }
}
