<?php
// database/seeders/KomplainSeeder.php
namespace Database\Seeders;

use App\Models\Komplain;
use App\Models\JenisKomplain;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class KomplainSeeder extends Seeder
{
    public function run(): void
    {
        // Buat beberapa komplain dengan data spesifik dan realistis
        $komplainData = [
            [
                'uuid' => substr(fake()->uuid(), 0, 8),
                'nomor_komplain' => 'KOM/2024/12/001',
                'tanggal_komplain' => '2024-12-01',
                'uraian_komplain' => 'Pelayanan di IGD sangat lambat, pasien menunggu lebih dari 2 jam untuk mendapatkan penanganan awal. Perawat terlihat kurang responsif terhadap keluhan pasien. Saat ditanya, perawat memberikan jawaban yang kurang memuaskan dan terkesan acuh tak acuh.',
                'keterangan' => 'Komplain disampaikan langsung oleh keluarga pasien',
                'lokasi_kejadian' => 'Instalasi Gawat Darurat (IGD)',
                'waktu_kejadian' => '2024-12-01 14:30:00',
                'media_komplain' => 'langsung',
                'kategori' => 'merah',
                'nama_pasien' => 'Budi Santoso',
                'no_rm_pasien' => '123456',
                'alamat_pasien' => 'Jl. Merdeka No. 123, Jakarta Pusat',
                'telp_pasien' => '081234567890',
                'disampaikan_oleh' => 'keluarga',
                'disampaikan_oleh_detail' => 'Istri pasien - Sari Dewi',
                'jenis_komplain_id' => 1, // PELAYANAN
                'status_komplain' => 'aktif',
                'submitted_at' => Carbon::parse('2024-12-01 15:00:00'),
                'units_notified_at' => Carbon::parse('2024-12-01 15:30:00'),
                'created_by' => 1,
            ],
            [
                'uuid' => substr(fake()->uuid(), 0, 8),
                'nomor_komplain' => 'KOM/2024/12/002',
                'tanggal_komplain' => '2024-12-02',
                'uraian_komplain' => 'Fasilitas kamar mandi di ruang rawat inap kelas 1 dalam kondisi kotor dan tidak terawat. Air tidak mengalir dengan baik, bau tidak sedap, dan tissue toilet habis tidak segera diganti. Kondisi ini sangat mengganggu kenyamanan pasien.',
                'keterangan' => 'Pasien sudah menyampaikan ke perawat ruangan tapi belum ada tindak lanjut',
                'lokasi_kejadian' => 'Ruang Rawat Inap Kelas 1, Kamar 201',
                'waktu_kejadian' => '2024-12-02 09:00:00',
                'media_komplain' => 'wa',
                'kategori' => 'kuning',
                'nama_pasien' => 'Siti Aminah',
                'no_rm_pasien' => '234567',
                'alamat_pasien' => 'Jl. Sudirman No. 456, Bandung',
                'telp_pasien' => '081234567891',
                'disampaikan_oleh' => 'ybs',
                'jenis_komplain_id' => 2, // FASILITAS
                'status_komplain' => 'aktif',
                'submitted_at' => Carbon::parse('2024-12-02 10:00:00'),
                'units_notified_at' => Carbon::parse('2024-12-02 10:15:00'),
                'created_by' => 1,
            ],
            [
                'uuid' => substr(fake()->uuid(), 0, 8),
                'nomor_komplain' => 'KOM/2024/12/003',
                'tanggal_komplain' => '2024-12-03',
                'uraian_komplain' => 'Proses administrasi pembayaran sangat rumit dan memakan waktu lama. Petugas kurang memberikan informasi yang jelas mengenai prosedur dan dokumen yang diperlukan. Harus bolak-balik beberapa kali untuk melengkapi berkas.',
                'keterangan' => 'Pasien sudah 3 kali datang ke bagian administrasi',
                'lokasi_kejadian' => 'Bagian Administrasi/Kasir Lantai 1',
                'waktu_kejadian' => '2024-12-03 11:15:00',
                'media_komplain' => 'email',
                'kategori' => 'hijau',
                'nama_pasien' => 'Ahmad Wijaya',
                'no_rm_pasien' => '345678',
                'alamat_pasien' => 'Jl. Diponegoro No. 789, Surabaya',
                'telp_pasien' => '081234567892',
                'disampaikan_oleh' => 'ybs',
                'jenis_komplain_id' => 3, // ADMINISTRASI
                'status_komplain' => 'aktif',
                'submitted_at' => Carbon::parse('2024-12-03 12:00:00'),
                'created_by' => 1,
            ],
            [
                'uuid' => substr(fake()->uuid(), 0, 8),
                'nomor_komplain' => 'KOM/2024/12/004',
                'tanggal_komplain' => '2024-12-04',
                'uraian_komplain' => 'Kebersihan toilet umum di area rawat jalan sangat buruk. Lantai licin karena tidak kering, tempat sampah penuh, dan aroma tidak sedap. Kondisi ini tidak layak untuk rumah sakit.',
                'lokasi_kejadian' => 'Toilet Umum Rawat Jalan Lantai 2',
                'waktu_kejadian' => '2024-12-04 13:45:00',
                'media_komplain' => 'langsung',
                'kategori' => 'kuning',
                'nama_pasien' => 'Rina Sari',
                'no_rm_pasien' => '456789',
                'alamat_pasien' => 'Jl. Veteran No. 321, Yogyakarta',
                'telp_pasien' => '081234567893',
                'disampaikan_oleh' => 'keluarga',
                'disampaikan_oleh_detail' => 'Anak pasien - Doni Sari',
                'jenis_komplain_id' => 4, // KEBERSIHAN
                'status_komplain' => 'aktif',
                'submitted_at' => Carbon::parse('2024-12-04 14:00:00'),
                'created_by' => 1,
            ],
            [
                'uuid' => substr(fake()->uuid(), 0, 8),
                'nomor_komplain' => 'KOM/2024/12/005',
                'tanggal_komplain' => '2024-12-05',
                'uraian_komplain' => 'Kualitas makanan pasien rawat inap sangat mengecewakan. Nasi keras, sayur tidak segar, dan rasa hambar. Porsi juga tidak sesuai dengan kebutuhan pasien yang sedang dalam masa penyembuhan.',
                'lokasi_kejadian' => 'Ruang Rawat Inap VIP, Kamar 301',
                'waktu_kejadian' => '2024-12-05 12:30:00',
                'media_komplain' => 'telepon',
                'kategori' => 'hijau',
                'nama_pasien' => 'Hendra Kurniawan',
                'no_rm_pasien' => '567890',
                'alamat_pasien' => 'Jl. Gatot Subroto No. 654, Medan',
                'telp_pasien' => '081234567894',
                'disampaikan_oleh' => 'keluarga',
                'disampaikan_oleh_detail' => 'Istri pasien - Maya Kurniawan',
                'jenis_komplain_id' => 5, // MAKANAN
                'status_komplain' => 'aktif',
                'submitted_at' => Carbon::parse('2024-12-05 13:00:00'),
                'created_by' => 1,
            ],
            [
                'uuid' => substr(fake()->uuid(), 0, 8),
                'nomor_komplain' => 'KOM/2024/11/015',
                'tanggal_komplain' => '2024-11-20',
                'uraian_komplain' => 'Area parkir sangat terbatas dan tidak teratur. Banyak kendaraan parkir sembarangan sehingga menyulitkan akses ambulans. Petugas parkir juga kurang mengatur dengan baik.',
                'lokasi_kejadian' => 'Area Parkir Utama RS',
                'waktu_kejadian' => '2024-11-20 08:30:00',
                'media_komplain' => 'wa',
                'kategori' => 'kuning',
                'nama_pasien' => 'Tono Suharjo',
                'no_rm_pasien' => '678901',
                'alamat_pasien' => 'Jl. Ahmad Yani No. 987, Semarang',
                'telp_pasien' => '081234567895',
                'disampaikan_oleh' => 'ybs',
                'jenis_komplain_id' => 6, // PARKIR
                'status_komplain' => 'selesai',
                'submitted_at' => Carbon::parse('2024-11-20 09:00:00'),
                'all_clarifications_completed_at' => Carbon::parse('2024-11-25 10:00:00'),
                'all_follow_ups_completed_at' => Carbon::parse('2024-11-28 14:00:00'),
                'final_resolution_at' => Carbon::parse('2024-11-30 16:00:00'),
                'created_by' => 1,
            ],
            [
                'uuid' => substr(fake()->uuid(), 0, 8),
                'nomor_komplain' => 'KOM/2024/11/022',
                'tanggal_komplain' => '2024-11-25',
                'uraian_komplain' => 'Biaya perawatan yang dikenakan tidak sesuai dengan tarif yang telah ditetapkan. Ada beberapa item yang ditagihkan padahal tidak digunakan oleh pasien. Perlu klarifikasi detail biaya.',
                'lokasi_kejadian' => 'Bagian Kasir/Billing',
                'waktu_kejadian' => '2024-11-25 15:20:00',
                'media_komplain' => 'email',
                'kategori' => 'merah',
                'nama_pasien' => 'Indira Sari',
                'no_rm_pasien' => '789012',
                'alamat_pasien' => 'Jl. Pahlawan No. 147, Malang',
                'telp_pasien' => '081234567896',
                'disampaikan_oleh' => 'keluarga',
                'disampaikan_oleh_detail' => 'Suami pasien - Budi Sari',
                'jenis_komplain_id' => 8, // BIAYA
                'status_komplain' => 'selesai',
                'submitted_at' => Carbon::parse('2024-11-25 16:00:00'),
                'all_clarifications_completed_at' => Carbon::parse('2024-11-28 11:00:00'),
                'all_follow_ups_completed_at' => Carbon::parse('2024-12-01 09:00:00'),
                'final_resolution_at' => Carbon::parse('2024-12-03 14:00:00'),
                'created_by' => 1,
            ],
        ];

        foreach ($komplainData as $data) {
            Komplain::create($data);
        }

        // Generate 13 komplain tambahan dengan factory untuk total 20
        $additionalKomplains = [];
        for ($i = 1; $i <= 13; $i++) {
            $month = fake()->numberBetween(10, 12);
            $day = fake()->numberBetween(1, 28);
            $tanggal = Carbon::create(2024, $month, $day);
            
            $additionalKomplains[] = [
                'uuid' => substr(fake()->uuid(), 0, 8),
                'nomor_komplain' => sprintf('KOM/2025/%02d/%03d', $month, $i + 7),
                'tanggal_komplain' => $tanggal->format('Y-m-d'),
                'uraian_komplain' => fake()->paragraph(4),
                'keterangan' => fake()->optional()->sentence(),
                'lokasi_kejadian' => fake()->randomElement([
                    'IGD', 'Rawat Inap Lt 2', 'Rawat Jalan', 'Laboratorium',
                    'Radiologi', 'Farmasi', 'Kasir', 'Area Parkir', 'Ruang Tunggu'
                ]),
                'waktu_kejadian' => $tanggal->addHours(fake()->numberBetween(8, 17))->addMinutes(fake()->numberBetween(0, 59)),
                'media_komplain' => fake()->randomElement(['langsung', 'wa', 'email', 'telepon', 'fb']),
                'kategori' => fake()->randomElement(['hijau', 'kuning', 'merah']),
                'nama_pasien' => fake()->name(),
                'no_rm_pasien' => fake()->regexify('[0-9]{6}'),
                'alamat_pasien' => fake()->address(),
                'telp_pasien' => fake()->phoneNumber(),
                'disampaikan_oleh' => fake()->randomElement(['ybs', 'keluarga', 'lainnya']),
                'disampaikan_oleh_detail' => fake()->optional()->name(),
                'jenis_komplain_id' => fake()->numberBetween(1, 9),
                'status_komplain' => fake()->randomElement(['aktif', 'selesai', 'batal']),
                'submitted_at' => $tanggal->addMinutes(30),
                'created_by' => 1,
            ];
        }

        foreach ($additionalKomplains as $data) {
            Komplain::create($data);
        }
    }
}