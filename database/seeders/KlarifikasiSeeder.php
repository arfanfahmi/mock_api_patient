<?php

namespace Database\Seeders;

use App\Models\Klarifikasi;
use App\Models\KomplainUnit;
use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class KlarifikasiSeeder extends Seeder
{
    public function run(): void
    {
        $komplainUnits = KomplainUnit::with(['komplain', 'unit'])->limit(5)->get();
        
        foreach ($komplainUnits as $komplainUnit) {
            $pegawai = Pegawai::inRandomOrder()->first();
            
            Klarifikasi::create([
                'uuid' => substr(fake()->uuid(), 0, 8),
                'komplain_id' => $komplainUnit->komplain_id,
                'unit_id' => $komplainUnit->unit_id,
                'tanggal_klarifikasi' => now(),
                'klarifikasi' => $this->generateKlarifikasi($komplainUnit),
                'filled_by_pegawai_id' => $pegawai->id,
                'filled_by_unit_id' => $komplainUnit->unit_id,
                'fill_authorization' => 'own_unit',
                'keterangan' => 'Klarifikasi diisi oleh unit terkait',
            ]);

            // Update status komplain unit
            $komplainUnit->update([
                'status_unit' => 'klarifikasi_selesai',
                'klarifikasi_at' => now(),
            ]);
        }
    }

    private function generateKlarifikasi($komplainUnit): string
    {
        $templates = [
            'Setelah melakukan investigasi internal, kami menemukan bahwa keluhan ini disebabkan oleh tingginya beban kerja pada waktu tersebut. Kami telah melakukan evaluasi dan akan melakukan perbaikan prosedur.',
            'Berdasarkan pengecekan lapangan, kondisi yang dilaporkan memang terjadi. Hal ini disebabkan oleh kerusakan peralatan yang sedang dalam proses perbaikan.',
            'Kami mengakui adanya kekurangan dalam pelayanan. Tim kami telah melakukan evaluasi dan mengidentifikasi area yang perlu diperbaiki.',
            'Keluhan yang disampaikan telah kami verifikasi. Kami menemukan bahwa hal ini terjadi karena kurangnya koordinasi antar unit.',
            'Setelah dilakukan investigasi menyeluruh, kami menemukan bahwa masalah ini disebabkan oleh faktor eksternal yang di luar kendali unit kami.',
        ];

        return fake()->randomElement($templates);
    }
}