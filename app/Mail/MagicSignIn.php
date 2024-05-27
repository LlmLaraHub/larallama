<?php

namespace App\Mail;

use App\Models\LoginToken;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MagicSignIn extends Mailable
{
    use Queueable, SerializesModels;

    public LoginToken $loginToken;

    public string $url;

    public string $app_name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(LoginToken $loginToken)
    {
        $this->loginToken = $loginToken;
        $this->url = $this->loginToken->signed_url;
        $this->app_name = config('app.name');
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Your Easy Login for '.$this->app_name,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'mail.magic-sign-in',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
