<?php

// database/seeders/HasilPenyelesaianSeeder.php
namespace Database\Seeders;

use App\Models\HasilPenyelesaian;
use App\Models\Komplain;
use Illuminate\Database\Seeder;

class HasilPenyelesaianSeeder extends Seeder
{
    public function run(): void
    {
        $selesaiKomplains = Komplain::where('status_komplain', 'selesai')->limit(2)->get();

        foreach ($selesaiKomplains as $komplain) {
            HasilPenyelesaian::create([
                'uuid' => substr(fake()->uuid(), 0, 8),
                'komplain_id' => $komplain->id,
                'tanggal_penyelesaian' => now(),
                'hasil_penyelesaian' => $this->generateHasilPenyelesaian($komplain),
                'status_penyelesaian' => fake()->randomElement(['selesai', 'belum_selesai']),
                'status_lainnya_detail' => fake()->optional()->sentence(),
                'created_by' => 1,
                'nama_humas' => 'Rina Sari',
                'kode_pegawai_humas' => 'HM00000001',
            ]);

            // Update status komplain menjadi selesai
            $komplain->update([
                'status_komplain' => 'selesai',
                'final_resolution_at' => now(),
            ]);
        }
    }

    private function generateHasilPenyelesaian($komplain): string
    {
        $templates = [
            "Komplain telah diselesaikan dengan baik. Semua unit terkait telah melakukan perbaikan sesuai rencana tindak lanjut. Pasien menyatakan puas dengan penyelesaian yang diberikan. Sistem monitoring telah ditingkatkan untuk mencegah kejadian serupa.",
            "Penyelesaian komplain dilakukan secara komprehensif dengan melibatkan semua stakeholder. Perbaikan fasilitas dan prosedur telah dilaksanakan. Follow up dengan pasien menunjukkan tingkat kepuasan yang baik. Akan dilakukan evaluasi berkala untuk memastikan sustainability.",
            "Komplain berhasil diselesaikan melalui koordinasi yang baik antar unit. Implementasi solusi telah dilakukan sepenuhnya. Pasien memberikan feedback positif atas penanganan komplain. Standard operating procedure telah diperbaharui untuk mencegah masalah serupa.",
        ];

        return fake()->randomElement($templates);
    }
}