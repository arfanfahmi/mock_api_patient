<?php

// database/seeders/KomplainAuditLogSeeder.php
namespace Database\Seeders;

use App\Models\KomplainAuditLog;
use App\Models\Komplain;
use App\Models\Unit;
use App\Models\Pegawai;
use App\Models\Klarifikasi;
use App\Models\RtlUnit;
use Illuminate\Database\Seeder;

class KomplainAuditLogSeeder extends Seeder
{
    public function run(): void
    {
        // Audit log untuk klarifikasi
        $klarifikasis = Klarifikasi::limit(3)->get();
        foreach ($klarifikasis as $klarifikasi) {
            KomplainAuditLog::create([
                'komplain_id' => $klarifikasi->komplain_id,
                'target_unit_id' => $klarifikasi->unit_id,
                'actor_pegawai_id' => $klarifikasi->filled_by_pegawai_id,
                'actor_unit_id' => $klarifikasi->filled_by_unit_id,
                'action_type' => 'klarifikasi',
                'authorization_type' => $klarifikasi->fill_authorization,
                'action_description' => "Mengisi klarifikasi untuk komplain dengan UUID {$klarifikasi->uuid}",
                'metadata' => json_encode([
                    'klarifikasi_id' => $klarifikasi->id,
                    'tanggal_klarifikasi' => $klarifikasi->tanggal_klarifikasi,
                    'ip_address' => fake()->ipv4(),
                ]),
            ]);
        }

        // Audit log untuk RTL Unit
        $rtlUnits = RtlUnit::limit(3)->get();
        foreach ($rtlUnits as $rtlUnit) {
            KomplainAuditLog::create([
                'komplain_id' => $rtlUnit->komplain_id,
                'target_unit_id' => $rtlUnit->unit_id,
                'actor_pegawai_id' => $rtlUnit->pic_pegawai_id,
                'actor_unit_id' => $rtlUnit->unit_id,
                'action_type' => 'rtl_unit',
                'authorization_type' => 'own_unit',
                'action_description' => "Membuat rencana tindak lanjut unit dengan PIC {$rtlUnit->pic_nama}",
                'metadata' => json_encode([
                    'rtl_id' => $rtlUnit->id,
                    'tanggal_rencana' => $rtlUnit->tanggal_rencana,
                    'pic_kode' => $rtlUnit->pic_kode_pegawai,
                ]),
            ]);
        }

        // Audit log untuk approval
        $komplains = Komplain::limit(2)->get();
        foreach ($komplains as $komplain) {
            $manajer = Pegawai::whereIn('id', [1, 2])->inRandomOrder()->first();
            $unit = Unit::inRandomOrder()->first();
            
            KomplainAuditLog::create([
                'komplain_id' => $komplain->id,
                'target_unit_id' => $unit->id,
                'actor_pegawai_id' => $manajer->id,
                'actor_unit_id' => $unit->id,
                'action_type' => 'approval_manajer',
                'authorization_type' => 'parent_unit',
                'action_description' => "Memberikan persetujuan manajer untuk komplain {$komplain->nomor_komplain}",
                'metadata' => json_encode([
                    'approval_status' => 'disetujui',
                    'approval_date' => now()->format('Y-m-d H:i:s'),
                ]),
            ]);
        }
    }
}