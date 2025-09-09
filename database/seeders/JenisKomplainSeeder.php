<?php
// database/seeders/JenisKomplainSeeder.php
namespace Database\Seeders;

use App\Models\JenisKomplain;
use Illuminate\Database\Seeder;

class JenisKomplainSeeder extends Seeder
{
    public function run(): void
    {
        $jenisKomplain = [
            [
                'kode_jenis' => 'PELAYANAN',
                'nama_jenis' => 'Pelayanan Medis',
                'deskripsi' => 'Keluhan terkait pelayanan medis dari dokter, perawat, atau tenaga medis lainnya',
                'is_active' => true,
            ],
            [
                'kode_jenis' => 'FASILITAS',
                'nama_jenis' => 'Fasilitas',
                'deskripsi' => 'Keluhan terkait fasilitas rumah sakit seperti kamar, ruang tunggu, dll',
                'is_active' => true,
            ],
            [
                'kode_jenis' => 'ADMINISTRASI',
                'nama_jenis' => 'Administrasi',
                'deskripsi' => 'Keluhan terkait proses administrasi pendaftaran, pembayaran, dll',
                'is_active' => true,
            ],
            [
                'kode_jenis' => 'KEBERSIHAN',
                'nama_jenis' => 'Kebersihan',
                'deskripsi' => 'Keluhan terkait kebersihan lingkungan rumah sakit',
                'is_active' => true,
            ],
            [
                'kode_jenis' => 'MAKANAN',
                'nama_jenis' => 'Layanan Makanan',
                'deskripsi' => 'Keluhan terkait kualitas makanan pasien',
                'is_active' => true,
            ],
            [
                'kode_jenis' => 'PARKIR',
                'nama_jenis' => 'Area Parkir',
                'deskripsi' => 'Keluhan terkait area parkir dan aksesibilitas',
                'is_active' => true,
            ],
            [
                'kode_jenis' => 'KEAMANAN',
                'nama_jenis' => 'Keamanan',
                'deskripsi' => 'Keluhan terkait keamanan lingkungan rumah sakit',
                'is_active' => true,
            ],
            [
                'kode_jenis' => 'BIAYA',
                'nama_jenis' => 'Biaya Perawatan',
                'deskripsi' => 'Keluhan terkait biaya perawatan dan tarif rumah sakit',
                'is_active' => true,
            ],
            [
                'kode_jenis' => 'LAINNYA',
                'nama_jenis' => 'Lainnya',
                'deskripsi' => 'Keluhan kategori lainnya yang tidak termasuk dalam kategori di atas',
                'is_active' => true,
            ],
        ];

        foreach ($jenisKomplain as $jenis) {
            JenisKomplain::create($jenis);
        }
    }
}