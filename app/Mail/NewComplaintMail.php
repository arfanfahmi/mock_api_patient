<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NewComplaintMail extends Mailable
{
    use Queueable, SerializesModels;

    var $komplain;

    /**
     * Create a new message instance.
     */
    public function __construct($komplain)
    {
        $this->komplain = $komplain;
    }

    public function build()
    {
        try {
            return $this->markdown('email.new_complaint_email')
                        ->with([
                            'komplain' => $this->komplain,
                            'logo' => public_path('storage/uploads/images/logo_pku_full_text.png'),
                            'link' => route('komplain.show_by_link', $this->komplain->uuid),
                        ]);
        } catch (\Exception $e) {
            Log::error('Error sending pemberitahuan komplain: ' . $e->getMessage());
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'HUMAS PKU - Pemberitahuan Komplain Baru',
        );
    }
}
