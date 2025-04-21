<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AprovacaoMail extends Mailable{
    use Queueable, SerializesModels;
    public $details;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details){
        $this->details = $details;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(){
        return $this
                    ->from($this->details['dados']['email_cliente'])
                    ->subject('Aprovação do Pedido')
                    ->view('emails.aprovacaoMail');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope{
        return new Envelope(
            from: $this->details['dados']['email_cliente'],
            subject: 'Aprovação do Pedido',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content{
        return new Content(
            view: 'emails.aprovacaoMail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array{
        return [];
    }
}
