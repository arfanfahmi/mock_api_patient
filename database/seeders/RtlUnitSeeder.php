<?php
namespace Database\Seeders;

use App\Models\RtlUnit;
use App\Models\KomplainUnit;
use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class RtlUnitSeeder extends Seeder
{
    public function run(): void
    {
        $komplainUnits = KomplainUnit::where('status_unit', 'klarifikasi_selesai')
                                    ->limit(5)
                                    ->get();

        foreach ($komplainUnits as $komplainUnit) {
            $pic = Pegawai::inRandomOrder()->first();

            RtlUnit::create([
                'uuid' => substr(fake()->uuid(), 0, 8),
                'komplain_id' => $komplainUnit->komplain_id,
                'unit_id' => $komplainUnit->unit_id,
                'tanggal_rencana' => now()->addDays(rand(7, 30)),
                'rtl_unit' => $this->generateRTL($komplainUnit),
                'pic_pegawai_id' => $pic->id,
                'pic_nama' => $pic->nama,
                'pic_kode_pegawai' => $pic->kode_pegawai,
                'keterangan' => 'Rencana tindak lanjut untuk mengatasi masalah',
            ]);

            // Update status komplain unit
            $komplainUnit->update([
                'status_unit' => 'tindak_lanjut_selesai',
                'tindak_lanjut_at' => now(),
            ]);
        }
    }

    private function generateRTL($komplainUnit): string
    {
        $templates = [
            '1. Melakukan pelatihan ulang kepada staff mengenai standar pelayanan\n2. Memperbaiki prosedur operasional standar\n3. Meningkatkan monitoring dan evaluasi harian\n4. Melakukan sosialisasi kepada seluruh tim',
            '1. Perbaikan fasilitas yang rusak dalam waktu 1 minggu\n2. Peningkatan maintenance rutin\n3. Pengadaan peralatan cadangan\n4. Evaluasi berkala kondisi fasilitas',
            '1. Streamlining proses administrasi\n2. Digitalisasi formulir dan dokumen\n3. Pelatihan staff untuk customer service\n4. Implementasi sistem antrian digital',
            '1. Peningkatan koordinasi antar unit\n2. Pembuatan SOP komunikasi internal\n3. Regular meeting koordinasi mingguan\n4. Sistem pelaporan terintegrasi',
            '1. Identifikasi dan mitigasi faktor eksternal\n2. Pembuatan contingency plan\n3. Koordinasi dengan pihak eksternal terkait\n4. Monitoring dan evaluasi berkelanjutan',
        ];

        return fake()->randomElement($templates);
    }
}