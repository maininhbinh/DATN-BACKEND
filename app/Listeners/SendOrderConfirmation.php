<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\SendOrder;
use App\Mail\SendOTP;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmation implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        //
        $orderDetail = $event->orderDetail;
        $toEmail = $event->toEmail;

        Mail::to($toEmail)->send(new SendOrder($orderDetail));
    }
}
