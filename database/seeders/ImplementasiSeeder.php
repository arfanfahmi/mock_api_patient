<?php

// database/seeders/ImplementasiSeeder.php
namespace Database\Seeders;

use App\Models\Implementasi;
use App\Models\Komplain;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ImplementasiSeeder extends Seeder
{
    public function run(): void
    {
      $komplains = Komplain::where('status_komplain', 'selesai')->limit(2)->get();

      foreach ($komplains as $komplain) {
        Implementasi::create([
          'uuid' => substr(fake()->uuid(), 0, 8),
          'komplain_id' => $komplain->id,
          'tanggal_implementasi' => now()->subDays(rand(1, 7)),
          'implementasi' => $this->generateImplementasi(),
          'keterangan' => fake()->optional()->sentence(),
          'created_by' => 1,
          'nama_humas' => 'Rina Sari',
          'kode_pegawai_humas' => 'HM00000001',
        ]);
      }
    }

    private function generateImplementasi(): string
    {
      $implementasiOptions = [
        'Telah dilakukan perbaikan sistem triase dan penambahan tenaga medis pada shift sibuk. Waktu tunggu rata-rata berhasil diturunkan dari 2 jam menjadi 45 menit. Monitoring real-time telah diterapkan.',
        'Renovasi fasilitas kamar mandi telah selesai dilaksanakan. Sistem maintenance harian telah ditingkatkan. Feedback pasien menunjukkan peningkatan kepuasan sebesar 80%.',
        'Implementasi sistem antrian digital dan perbaikan alur pelayanan. Waktu tunggu berkurang signifikan. Staff telah dilatih untuk memberikan informasi yang lebih jelas kepada pasien.',
        'Digitalisasi proses pembayaran dan simplifikasi prosedur administrasi. Waktu proses pembayaran berkurang dari 30 menit menjadi 10 menit. Training staff customer service telah dilakukan.',
        'Peningkatan sistem maintenance preventif dan pengadaan spare part. Koordinasi dengan vendor untuk response time yang lebih cepat. Sistem monitoring fasilitas telah diperbaharui.',
      ];

      return $implementasiOptions[array_rand($implementasiOptions)];
    }
}