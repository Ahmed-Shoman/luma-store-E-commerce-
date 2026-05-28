<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderCreatedMail;
use Illuminate\Support\Facades\Mail;

class SendOrderCreatedEmail
{
    public function handle(OrderCreated $event)
    {
        Mail::to($event->order->customer_phone . '@example.com')
            ->send(new OrderCreatedMail($event->order));
    }
}
