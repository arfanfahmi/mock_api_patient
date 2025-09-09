<?php
// database/seeders/RtlHumasSeeder.php
namespace Database\Seeders;

use App\Models\RtlHumas;
use App\Models\Komplain;
use Illuminate\Database\Seeder;

class RtlHumasSeeder extends Seeder
{
    public function run(): void
    {
        $komplains = Komplain::where('status_komplain', 'aktif')->limit(3)->get();

        foreach ($komplains as $komplain) {
            RtlHumas::create([
                'uuid' => substr(fake()->uuid(), 0, 8),
                'komplain_id' => $komplain->id,
                'tanggal_rencana' => now()->addDays(rand(14, 60)),
                'rencana_tindak_lanjut' => $this->generateRTLHumas($komplain),
                'created_by' => 1,
                'nama_humas' => 'Rina Sari',
                'kode_pegawai_humas' => 'HM00000001',
                'keterangan' => 'Rencana tindak lanjut dari bagian Humas',
            ]);
        }
    }

    private function generateRTLHumas($komplain): string
    {
        $templates = [
            "1. Follow up dengan pasien/keluarga dalam 3 hari kerja\n2. Koordinasi dengan unit terkait untuk implementasi perbaikan\n3. Monitoring progress implementasi mingguan\n4. Evaluasi kepuasan pasien setelah implementasi\n5. Dokumentasi dan pelaporan hasil tindak lanjut",
            "1. Penyampaian hasil investigasi kepada pasien/keluarga\n2. Permintaan maaf resmi dari manajemen\n3. Implementasi sistem pencegahan berulang\n4. Sosialisasi perbaikan kepada seluruh staff\n5. Monitoring dan evaluasi berkelanjutan",
            "1. Komunikasi intensif dengan pasien untuk klarifikasi\n2. Koordinasi antar unit untuk solusi terintegrasi\n3. Pembuatan timeline implementasi yang jelas\n4. Regular update progress kepada pasien\n5. Quality assurance untuk memastikan perbaikan",
        ];

        return fake()->randomElement($templates);
    }
}