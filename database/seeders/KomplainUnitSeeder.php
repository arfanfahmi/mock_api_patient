<?php
// database/seeders/KomplainUnitSeeder.php
namespace Database\Seeders;

use App\Models\KomplainUnit;
use App\Models\Komplain;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class KomplainUnitSeeder extends Seeder
{
    public function run(): void
    {
        $komplains = Komplain::limit(10)->get(); // Ambil 10 komplain pertama

        foreach ($komplains as $komplain) {
            // Tentukan unit yang terkait berdasarkan jenis komplain
            $relatedUnits = $this->getRelatedUnits($komplain->jenis_komplain_id);
            
            foreach ($relatedUnits as $index => $unitId) {
                KomplainUnit::create([
                    'komplain_id' => $komplain->id,
                    'unit_id' => $unitId,
                    'is_primary_target' => $index === 0, // Unit pertama adalah primary
                    'workflow_level' => $this->getWorkflowLevel($unitId),
                    'workflow_order' => $index + 1,
                    'status_unit' => 'menunggu_klarifikasi',
                    'notified_at' => now(),
                ]);
            }
        }
    }
    private function getRelatedUnits($jenisKomplainId): array
    {
        // Mapping jenis komplain ke unit terkait
        $mapping = [
            1 => [5, 6, 7], // PELAYANAN -> IGD, Rawat Inap, Rawat Jalan
            2 => [12, 6, 7], // FASILITAS -> Logistik, Rawat Inap, Rawat Jalan
            3 => [10, 3], // ADMINISTRASI -> Keuangan, Div Administrasi
            4 => [12, 5, 6], // KEBERSIHAN -> Logistik, IGD, Rawat Inap
            5 => [12], // MAKANAN -> Logistik
            6 => [12], // PARKIR -> Logistik
            7 => [11, 12], // KEAMANAN -> SDM, Logistik
            8 => [10, 3], // BIAYA -> Keuangan, Div Administrasi
            9 => [4], // LAINNYA -> Humas
        ];

        return $mapping[$jenisKomplainId] ?? [4]; // Default ke Humas
    }

    private function getWorkflowLevel($unitId): string
    {
        $unit = Unit::find($unitId);
        return match ($unit->level_unit) {
            4 => 'subunit',
            3 => 'unit',
            1, 2 => 'manager',
            default => 'unit'
        };
    }
}