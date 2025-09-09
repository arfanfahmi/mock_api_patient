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

class PermintaanPencairanAnggaranMail extends Mailable
{
    use Queueable, SerializesModels;

    var $realisasi;
    var $unit;
    var $nilaiPengajuan;
    var $role;

    /**
     * Create a new message instance.
     */
    public function __construct($realisasi, $unit, $nilaiPengajuan, $role)
    {
        $this->realisasi = $realisasi;
        $this->unit = $unit;
        $this->nilaiPengajuan = $nilaiPengajuan;
        $this->role = $role;
    }

    public function build()
    {
        try {
            return $this->markdown('email.permintaan_pencairan_anggaran_email')
                        ->with([
                            'realisasi' => $this->realisasi,
                            'logo' => public_path('storage/uploads/images/logo_pku_full_text.png'),
                            'unit' => $this->unit,
                            'link' => $this->role == 'pegawai' ? route('approval.show', $this->realisasi->uuid) : route('ramah.approval.show', $this->realisasi->uuid),
                            'nilaiPengajuan' => $this->nilaiPengajuan
                        ]);
        } catch (\Exception $e) {
            Log::error('Error sending approval email: ' . $e->getMessage());
            // Optionally, you can rethrow the exception to allow it to bubble up and be caught elsewhere
            // throw $e;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        //no pengajuan
        $explodedNoRealisasi = explode('/', $this->realisasi->no_realisasi);
        $noRealisasi = $explodedNoRealisasi[2];

        return new Envelope(
            subject: 'Permintaan Pencairan Anggaran #'.$noRealisasi.' '.$this->unit->nama_unit,
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
