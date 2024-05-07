<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $message;
    public $username;

    /**
     * Create a new message instance.
     */
    public function __construct($message, $username)
    {   
        //
        $this->message = $message;
        $this->username = $username;
    }

    public function build()
    {
        $data = $this->message;
        $username = $this->username;
        return $this->from(env('MAIL_FROM_ADDRESS'))
        ->view('emails.welcome')
        ->with([
            'OTP' => $data,
            'name' => $username
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
