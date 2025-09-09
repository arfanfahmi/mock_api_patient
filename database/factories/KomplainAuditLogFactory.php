<?php
// database/factories/KomplainAuditLogFactory.php
namespace Database\Factories;

use App\Models\KomplainAuditLog;
use App\Models\Komplain;
use App\Models\Unit;
use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Factories\Factory;

class KomplainAuditLogFactory extends Factory
{
    protected $model = KomplainAuditLog::class;

    public function definition(): array
    {
        $actionTypes = [
            'klarifikasi' => 'Mengisi klarifikasi komplain',
            'rtl_unit' => 'Membuat rencana tindak lanjut unit',
            'approval_unit' => 'Memberikan persetujuan unit',
            'approval_manajer' => 'Memberikan persetujuan manajer',
            'delegasi' => 'Mendelegasikan tugas',
        ];

        $actionType = $this->faker->randomElement(array_keys($actionTypes));

        return [
            'komplain_id' => Komplain::factory(),
            'target_unit_id' => Unit::factory(),
            'actor_pegawai_id' => Pegawai::factory(),
            'actor_unit_id' => Unit::factory(),
            'action_type' => $actionType,
            'authorization_type' => $this->faker->randomElement([
                'own_unit',
                'parent_unit',
                'delegated',
                'emergency'
            ]),
            'action_description' => $actionTypes[$actionType],
            'metadata' => json_encode([
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
                'timestamp' => $this->faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
            ]),
        ];
    }
}
