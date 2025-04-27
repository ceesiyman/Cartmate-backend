<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $type;
    public $message;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, string $type, string $message = null)
    {
        $this->order = $order;
        $this->type = $type;
        $this->message = $message;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = match($this->type) {
            'new_order' => 'New Order Received',
            'status_update' => 'Order Status Updated',
            'admin_update' => 'Order Update from Admin',
            default => 'Order Notification'
        };

        return $this->markdown('emails.order-notification')
                    ->subject($subject);
    }
} 