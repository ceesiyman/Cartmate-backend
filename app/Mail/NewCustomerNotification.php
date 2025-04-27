<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class NewCustomerNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;

    /**
     * Create a new message instance.
     */
    public function __construct(User $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->markdown('emails.new-customer')
                    ->subject('New Customer Registration');
    }
} 