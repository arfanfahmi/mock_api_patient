<?php
// database/seeders/ApprovalSeeder.php
namespace Database\Seeders;

use App\Models\ApprovalUnit;
use App\Models\ApprovalManajer;
use App\Models\KomplainUnit;
use App\Models\Pegawai;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $komplainUnits = KomplainUnit::where('status_unit', 'tindak_lanjut_selesai')
                                    ->limit(3)
                                    ->get();

        foreach ($komplainUnits as $komplainUnit) {
            // Jika unit level 4 (subunit), buat approval unit
            $unit = Unit::find($komplainUnit->unit_id);
            if ($unit->level_unit == 4 && $unit->parent_unit) {
                $parentUnit = Unit::find($unit->parent_unit);
                $approver = Pegawai::inRandomOrder()->first();

                ApprovalUnit::create([
                    'uuid' => substr(fake()->uuid(), 0, 8),
                    'komplain_id' => $komplainUnit->komplain_id,
                    'subunit_id' => $unit->id,
                    'parent_unit_id' => $parentUnit->id,
                    'approver_pegawai_id' => $approver->id,
                    'approver_nama' => $approver->nama,
                    'approver_kode_pegawai' => $approver->kode_pegawai,
                    'status_approval' => fake()->randomElement(['menunggu_approval', 'disetujui', 'ditolak']),
                    'catatan_approval' => fake()->sentence(),
                    'tanggal_approval' => fake()->boolean(70) ? now() : null,
                ]);
            }

            // Buat approval manajer
            $manajer = Pegawai::whereIn('id', [1, 2])->inRandomOrder()->first(); // Pegawai management

            ApprovalManajer::create([
                'uuid' => substr(fake()->uuid(), 0, 8),
                'komplain_id' => $komplainUnit->komplain_id,
                'unit_id' => $komplainUnit->unit_id,
                'manajer_pegawai_id' => $manajer->id,
                'manajer_nama' => $manajer->nama,
                'manajer_kode_pegawai' => $manajer->kode_pegawai,
                'status_approval' => fake()->randomElement(['menunggu_approval', 'disetujui', 'ditolak']),
                'catatan_manajer' => fake()->paragraph(),
                'tanggal_approval' => fake()->boolean(60) ? now() : null,
            ]);

            // Update status komplain unit
            if (fake()->boolean(70)) {
                $komplainUnit->update([
                    'status_unit' => 'menunggu_approval_manajer',
                    'submitted_to_manager_at' => now(),
                ]);
            }
        }
    }
}