<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderCreatedMail;
use Illuminate\Support\Facades\Mail;

class SendOrderCreatedEmail
{
    public function handle(OrderCreated $event): void
    {
        logger()->info('Order email fired: ' . $event->order->id);

        Mail::to(env('ORDER_RECEIVER_EMAIL'))
            ->send(new OrderCreatedMail($event->order));
    }
}
