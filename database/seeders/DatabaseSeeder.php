<?php
// Updated DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        $this->command->info('📅 Seeding Role...');
        $this->call(RoleSeeder::class);

         \App\Models\User::create([
            'id' => 1,
            'username'=>'ramah',
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'uuid' => fake()->uuid(),
            'aktif' =>true,
            'role_id' => 1, // superadmin
            'password' => PASSWORD_HASH('ramah'.'72928&*&^@&@*!&!^@&@^#^&!*!PKUKeramahanSebenarnya*123##123*&%$@', PASSWORD_BCRYPT),
            'remember_token' => Str::random(10),
        ]);

        
        $this->command->info('📅 Seeding Tahun...');
        $this->call(TahunSeeder::class);
        
        $this->command->info('👥 Seeding Pegawai...');
        $this->call(PegawaiSeeder::class);
        
        $this->command->info('🏢 Seeding Unit...');
        $this->call(UnitSeeder::class);
        
        $this->command->info('📋 Seeding Jenis Komplain...');
        $this->call(JenisKomplainSeeder::class);

        // Transactional data
        $this->command->info('📝 Seeding Komplain...');
        $this->call(KomplainSeeder::class);
        
        $this->command->info('🔗 Seeding Komplain Units...');
        $this->call(KomplainUnitSeeder::class);
        
        $this->command->info('💭 Seeding Klarifikasi...');
        $this->call(KlarifikasiSeeder::class);
        
        $this->command->info('📋 Seeding RTL Unit...');
        $this->call(RtlUnitSeeder::class);
        
        $this->command->info('✅ Seeding Approval...');
        $this->call(ApprovalSeeder::class);
        
        $this->command->info('📋 Seeding RTL Humas...');
        $this->call(RtlHumasSeeder::class);
        
        $this->command->info('✨ Seeding Hasil Penyelesaian...');
        $this->call(HasilPenyelesaianSeeder::class);
        
        $this->command->info('⚡ Seeding Implementasi...');
        $this->call(ImplementasiSeeder::class);

        // System data
        $this->command->info('🔐 Seeding Workflow Permissions...');
        $this->call(WorkflowPermissionSeeder::class);
        
        $this->command->info('🔔 Seeding Notifications...');
        $this->call(NotificationSeeder::class);
        
        $this->command->info('📊 Seeding Audit Logs...');
        $this->call(KomplainAuditLogSeeder::class);

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Tahun: 4 records');
        $this->command->info('   - Pegawai: 50 records');
        $this->command->info('   - Unit: 15 records');
        $this->command->info('   - Jenis Komplain: 9 records');
        $this->command->info('   - Komplain: 20 records');
        $this->command->info('   - Supporting data: Multiple tables');
    }
}