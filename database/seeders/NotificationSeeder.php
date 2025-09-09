<?php

// database/seeders/NotificationSeeder.php
namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Komplain;
use App\Models\Unit;
use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $komplains = Komplain::limit(5)->get();
        $units = Unit::limit(10)->get();
        $pegawais = Pegawai::limit(10)->get();

        foreach ($komplains as $komplain) {
            // Notifikasi ke unit terkait
            foreach ($units->random(3) as $unit) {
                Notification::create([
                    'komplain_id' => $komplain->id,
                    'unit_id' => $unit->id,
                    'pegawai_id' => $pegawais->random()->id,
                    'user_id' => 1,
                    'notification_type' => 'new_complaint',
                    'message' => "Komplain baru #{$komplain->nomor_komplain} membutuhkan perhatian unit {$unit->nama_unit}",
                    'is_read' => fake()->boolean(30),
                    'read_at' => fake()->boolean(30) ? now() : null,
                ]);
            }

            // Notifikasi need clarification
            Notification::create([
                'komplain_id' => $komplain->id,
                'unit_id' => $units->random()->id,
                'pegawai_id' => $pegawais->random()->id,
                'notification_type' => 'need_clarification',
                'message' => "Komplain #{$komplain->nomor_komplain} membutuhkan klarifikasi dari unit Anda",
                'is_read' => fake()->boolean(50),
                'read_at' => fake()->boolean(50) ? now() : null,
            ]);
        }
    }
}