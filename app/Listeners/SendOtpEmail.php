<?php

namespace App\Listeners;

use App\Events\OtpRequested;
use App\Mail\SendOTP;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOtpEmail implements ShouldQueue
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
    public function handle(OtpRequested $event): void
    {
        //
        Mail::to($event->email)->send(new SendOTP($event->otp, $event->content, $event->title));
    }
}
