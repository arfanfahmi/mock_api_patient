<?php
namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        // Buat pegawai khusus untuk management
        $pegawaiManagement = [
            [
                'kode_pegawai' => 'MG00000001',
                'uuid' => fake()->uuid(),
                'nama' => 'Dr. Siti Nurhaliza',
                'email' => 'direktur@hospital.com',
                'aktif' => true,
            ],
            [
                'kode_pegawai' => 'MG00000002',
                'uuid' => fake()->uuid(),
                'nama' => 'Dr. Ahmad Rahman',
                'email' => 'wadir@hospital.com',
                'aktif' => true,
            ],
            [
                'kode_pegawai' => 'HM00000001',
                'uuid' => fake()->uuid(),
                'nama' => 'Rina Sari',
                'email' => 'humas@hospital.com',
                'aktif' => true,
            ],
        ];

        foreach ($pegawaiManagement as $pegawai) {
            Pegawai::create($pegawai);
        }

        // Buat 47 pegawai random lainnya untuk total 50
        Pegawai::factory(47)->create();
    }
}