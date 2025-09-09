<?php
namespace Database\Seeders;

use App\Models\Tahun;
use Illuminate\Database\Seeder;

class TahunSeeder extends Seeder
{
    public function run(): void
    {
        $tahuns = [
            ['kode' => 'TH2022', 'nama_tahun' => 2022, 'keterangan' => 'Tahun 2022'],
            ['kode' => 'TH2023', 'nama_tahun' => 2023, 'keterangan' => 'Tahun 2023'],
            ['kode' => 'TH2024', 'nama_tahun' => 2024, 'keterangan' => 'Tahun 2024'],
            ['kode' => 'TH2025', 'nama_tahun' => 2025, 'keterangan' => 'Tahun 2025'],
        ];

        foreach ($tahuns as $tahun) {
            Tahun::create($tahun);
        }
    }
}