<?php
// database/seeders/WorkflowPermissionSeeder.php
namespace Database\Seeders;

use App\Models\WorkflowPermission;
use App\Models\Komplain;
use App\Models\Unit;
use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class WorkflowPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $komplains = Komplain::limit(5)->get();
        
        foreach ($komplains as $komplain) {
            // Untuk setiap komplain, buat workflow permission
            $units = Unit::whereIn('level_unit', [3, 4])->limit(3)->get();
            
            foreach ($units as $targetUnit) {
                // Permission untuk unit sendiri
                WorkflowPermission::create([
                    'komplain_id' => $komplain->id,
                    'target_unit_id' => $targetUnit->id,
                    'authorized_unit_id' => $targetUnit->id,
                    'authorized_pegawai_id' => null,
                    'can_klarifikasi' => true,
                    'can_rtl_unit' => true,
                    'can_approval' => false,
                    'authorization_reason' => 'own_unit',
                ]);

                // Permission untuk parent unit (jika ada)
                if ($targetUnit->parent_unit) {
                    $parentUnit = Unit::find($targetUnit->parent_unit);
                    WorkflowPermission::create([
                        'komplain_id' => $komplain->id,
                        'target_unit_id' => $targetUnit->id,
                        'authorized_unit_id' => $parentUnit->id,
                        'authorized_pegawai_id' => null,
                        'can_klarifikasi' => true,
                        'can_rtl_unit' => true,
                        'can_approval' => true,
                        'authorization_reason' => 'parent_unit',
                    ]);
                }

                // Permission untuk specific pegawai (delegasi)
                if (fake()->boolean(30)) {
                    $delegatedPegawai = Pegawai::inRandomOrder()->first();
                    WorkflowPermission::create([
                        'komplain_id' => $komplain->id,
                        'target_unit_id' => $targetUnit->id,
                        'authorized_unit_id' => $targetUnit->id,
                        'authorized_pegawai_id' => $delegatedPegawai->id,
                        'can_klarifikasi' => true,
                        'can_rtl_unit' => false,
                        'can_approval' => false,
                        'authorization_reason' => 'delegated',
                    ]);
                }
            }
        }
    }
}