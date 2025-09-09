<?php
namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        // Unit Level 1 (Top Level)
        $unitLevel1 = [
            [
                'unit_id' => 1,
                'kode_unit' => 'DIR001',
                'nama_unit' => 'Direktorat',
                'parent_unit' => null,
                'level_unit' => 1,
                'tipe_unit' => 'administrasi',
                'nama_pimpinan' => 'Dr. Siti Nurhaliza',
                'kode_pegawai' => 'MG00000001',
                'nama_jabatan' => 'Direktur',
            ],
            [
                'unit_id' => 2,
                'kode_unit' => 'MED001',
                'nama_unit' => 'Divisi Pelayanan Medis',
                'parent_unit' => 1,
                'level_unit' => 2,
                'tipe_unit' => 'medis',
                'nama_pimpinan' => 'Dr. Ahmad Rahman',
                'kode_pegawai' => 'MG00000002',
                'nama_jabatan' => 'Kepala Divisi Medis',
            ],
            [
                'unit_id' => 3,
                'kode_unit' => 'ADM001',
                'nama_unit' => 'Divisi Administrasi',
                'parent_unit' => 1,
                'level_unit' => 2,
                'tipe_unit' => 'administrasi',
            ],
            [
                'unit_id' => 4,
                'kode_unit' => 'HUM001',
                'nama_unit' => 'Bagian Humas',
                'parent_unit' => 3,
                'level_unit' => 3,
                'tipe_unit' => 'support',
                'nama_pimpinan' => 'Rina Sari',
                'kode_pegawai' => 'HM00000001',
                'nama_jabatan' => 'Kepala Humas',
            ],
        ];

        foreach ($unitLevel1 as $unit) {
            Unit::create($unit);
        }

        // Unit Level 2 & 3 (Sub Units)
        $subUnits = [
            // Sub unit dari Divisi Pelayanan Medis
            [
                'unit_id' => 5,
                'kode_unit' => 'IGD001',
                'nama_unit' => 'Instalasi Gawat Darurat',
                'parent_unit' => 2,
                'level_unit' => 3,
                'tipe_unit' => 'medis',
                'shift' => true,
            ],
            [
                'unit_id' => 6,
                'kode_unit' => 'RWI001',
                'nama_unit' => 'Rawat Inap',
                'parent_unit' => 2,
                'level_unit' => 3,
                'tipe_unit' => 'medis',
                'shift' => true,
            ],
            [
                'unit_id' => 7,
                'kode_unit' => 'RWJ001',
                'nama_unit' => 'Rawat Jalan',
                'parent_unit' => 2,
                'level_unit' => 3,
                'tipe_unit' => 'medis',
            ],
            [
                'unit_id' => 8,
                'kode_unit' => 'LAB001',
                'nama_unit' => 'Laboratorium',
                'parent_unit' => 2,
                'level_unit' => 3,
                'tipe_unit' => 'support',
            ],
            [
                'unit_id' => 9,
                'kode_unit' => 'RAD001',
                'nama_unit' => 'Radiologi',
                'parent_unit' => 2,
                'level_unit' => 3,
                'tipe_unit' => 'support',
            ],
            // Sub unit dari Divisi Administrasi
            [
                'unit_id' => 10,
                'kode_unit' => 'KEU001',
                'nama_unit' => 'Bagian Keuangan',
                'parent_unit' => 3,
                'level_unit' => 3,
                'tipe_unit' => 'administrasi',
            ],
            [
                'unit_id' => 11,
                'kode_unit' => 'SDM001',
                'nama_unit' => 'Bagian SDM',
                'parent_unit' => 3,
                'level_unit' => 3,
                'tipe_unit' => 'administrasi',
            ],
            [
                'unit_id' => 12,
                'kode_unit' => 'LOG001',
                'nama_unit' => 'Bagian Logistik',
                'parent_unit' => 3,
                'level_unit' => 3,
                'tipe_unit' => 'support',
            ],
        ];

        foreach ($subUnits as $unit) {
            Unit::create($unit);
        }

        // Buat beberapa unit level 4 (sub-sub unit)
        $subSubUnits = [
            [
                'unit_id' => 13,
                'kode_unit' => 'VIP001',
                'nama_unit' => 'Ruang VIP',
                'parent_unit' => 6, // parent ke Rawat Inap
                'level_unit' => 4,
                'tipe_unit' => 'medis',
                'shift' => true,
            ],
            [
                'unit_id' => 14,
                'kode_unit' => 'KLS001',
                'nama_unit' => 'Ruang Kelas 1',
                'parent_unit' => 6,
                'level_unit' => 4,
                'tipe_unit' => 'medis',
                'shift' => true,
            ],
            [
                'unit_id' => 15,
                'kode_unit' => 'POL001',
                'nama_unit' => 'Poliklinik Dalam',
                'parent_unit' => 7, // parent ke Rawat Jalan
                'level_unit' => 4,
                'tipe_unit' => 'medis',
            ],
        ];

        foreach ($subSubUnits as $unit) {
            Unit::create($unit);
        }
    }
}