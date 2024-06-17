<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOTP extends Mailable
{
    use Queueable, SerializesModels;

    private $otp;
    private $content;
    private $title;


    /**
     * Create a new message instance.
     */
    public function __construct($otp, $content, $title)
    {
        //
        $this->otp = $otp;
        $this->content = $content;
        $this->title = $title;
    }

    public function build()
    {
        $otp = $this->otp;
        $content = $this->content;
        $title = $this->title;

        return $this
        ->from(env('MAIL_FROM_ADDRESS'))
        ->view('emails.email')
        ->with([
            'title' => $title,
            'OTP' => $otp,
            'content' => $content
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.email'
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
}
