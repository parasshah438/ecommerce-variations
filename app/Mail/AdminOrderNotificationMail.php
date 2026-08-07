<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminOrderNotificationMail extends Mailable implements ShouldQueue
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
        return new Envelope(
            subject: 'New Order ' . $this->order->order_number . ' Received – ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.admin-order-notification',
            text: 'emails.admin-order-notification-text',
            with: [
                'order' => $this->order,
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
                'adminOrderUrl' => $this->adminOrderUrl(),
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
     * Build the admin order detail URL.
     */
    protected function adminOrderUrl(): string
    {
        $base = rtrim(config('app.url'), '/');

        return $base . '/admin/orders/' . $this->order->id;
    }
}
