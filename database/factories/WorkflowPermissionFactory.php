<?php
// database/factories/WorkflowPermissionFactory.php
namespace Database\Factories;

use App\Models\WorkflowPermission;
use App\Models\Komplain;
use App\Models\Unit;
use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowPermissionFactory extends Factory
{
    protected $model = WorkflowPermission::class;

    public function definition(): array
    {
        return [
            'komplain_id' => Komplain::factory(),
            'target_unit_id' => Unit::factory(),
            'authorized_unit_id' => Unit::factory(),
            'authorized_pegawai_id' => Pegawai::factory(),
            'can_klarifikasi' => $this->faker->boolean(70),
            'can_rtl_unit' => $this->faker->boolean(60),
            'can_approval' => $this->faker->boolean(50),
            'authorization_reason' => $this->faker->randomElement([
                'own_unit',
                'parent_unit',
                'koordinasi_unit',
                'delegated',
                'emergency'
            ]),
        ];
    }
}