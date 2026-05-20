<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMemberMail extends Mailable
{
    use Queueable, SerializesModels;

    public $member;
    public $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct($member, $pdfPath)
    {
        $this->member = $member;
        $this->pdfPath = $pdfPath;
    }

     public function build()
    {
        return $this->subject('Welcome to Our Organization')
            ->view('emails.welcome_member')
            ->attach($this->pdfPath, [
                'as' => 'Welcome_Letter_'.$this->member->member_no.'.pdf',
                'mime' => 'application/pdf',
            ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome Member Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
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
