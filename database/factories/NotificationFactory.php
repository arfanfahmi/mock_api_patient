<?php
namespace Database\Factories;

use App\Models\Notification;
use App\Models\Komplain;
use App\Models\Unit;
use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $notificationTypes = [
            'new_complaint' => 'Komplain baru telah dibuat',
            'need_clarification' => 'Komplain membutuhkan klarifikasi',
            'manager_approval' => 'Komplain membutuhkan persetujuan manajer',
            'status_update' => 'Status komplain telah diperbarui',
            'completion' => 'Komplain telah selesai',
        ];

        $type = $this->faker->randomElement(array_keys($notificationTypes));

        return [
            'komplain_id' => Komplain::factory(),
            'unit_id' => Unit::factory(),
            'pegawai_id' => Pegawai::factory(),
            'user_id' => 1,
            'notification_type' => $type,
            'message' => $notificationTypes[$type],
            'is_read' => $this->faker->boolean(30),
            'read_at' => $this->faker->optional(30)->dateTimeThisMonth(),
        ];
    }
}