<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class VerifikasiMail extends Mailable
{
    use Queueable, SerializesModels;

    var $pegawai;
    var $token;

    /**
     * Create a new message instance.
     */
    public function __construct($pegawai, $token)
    {
        $this->pegawai = $pegawai;
        $this->token = $token;
    }

    public function build()
    {
        return $this->markdown('email.verifikasi_email')
                    ->with([
                        'logo' => public_path('storage/uploads/images/logo_pku_full_text.png'),
                        'pegawai' => $this->pegawai,
                        'link' => url('pegawai/verifikasi_email/cek_token/'.$this->token),
                        'expiredTime' => Carbon::now()->addHours(24)->translatedFormat('d F Y').' pukul '.Carbon::now()->addHours(24)->translatedFormat('H.i')
                    ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifikasi email SISDI',
        );
    }

    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'email-sertifikat',
    //     );
    // }

    // /**
    //  * Get the attachments for the message.
    //  *
    //  * @return array<int, \Illuminate\Mail\Mailables\Attachment>
    //  */
    // public function attachments(): array
    // {
    //     return [];
    // }
}
