<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $customerName = $this->customerName();

        return new Envelope(
            subject: 'Order ' . $this->order->order_number . ' Confirmed – Thank You for Shopping with ' . config('app.name') . '!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.order-confirmation',
            text: 'emails.order-confirmation-text',
            with: [
                'order' => $this->order,
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
                'trackUrl' => $this->trackUrl(),
                'customerName' => $this->customerName(),
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
     * Display name for the customer greeting.
     */
    protected function customerName(): string
    {
        return $this->order->address?->name
            ?? $this->order->user?->name
            ?? 'Valued Customer';
    }

    /**
     * Build the public tracking URL for this order.
     *
     * The public-track route (order.track.public.details) looks orders up by
     * their numeric primary key via `findOrFail($orderNumber)`, so we must use
     * $order->id here — NOT $order->order_number, which is a display string
     * (e.g. "#000123") and would produce a broken 404 link.
     */
    protected function trackUrl(): string
    {
        $base = rtrim(config('app.url'), '/');

        if ($this->order->id) {
            return $base . '/track-order/' . $this->order->id;
        }

        return $base . '/track-order';
    }
}
