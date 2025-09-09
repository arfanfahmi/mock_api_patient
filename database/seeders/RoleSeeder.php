<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id'=>1, 'role_name'=>'superadmin', 'aktif'=>1, 'is_superadmin' => 1],
            ['id'=>2, 'role_name'=>'diklat', 'aktif'=>1, 'is_superadmin' => 0],
            ['id'=>3, 'role_name'=>'investasi', 'aktif'=>1, 'is_superadmin' => 0],
            ['id'=>4, 'role_name'=>'rutin', 'aktif'=>1, 'is_superadmin' => 0],
            ['id'=>5, 'role_name'=>'keuangan', 'aktif'=>1, 'is_superadmin' => 0],
        ]);
    }
}
