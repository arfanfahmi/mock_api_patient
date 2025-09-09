<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    \App\Models\User::create([
      'id' => 1,
      'username'=>'ramah',
      'nama' => fake()->name(),
      'email' => fake()->unique()->safeEmail(),
      'email_verified_at' => now(),
      'uuid' => fake()->uuid(),
      'aktif' =>true,
      'role_id' => 1, // superadmin
      'password' => password_hash('ramah123'.'72928&*&^@&@*!&!^@&@^#^&!*!PKUKeramahanSebenarnya*123##123*&%$@', PASSWORD_BCRYPT),
      'remember_token' => Str::random(10),
    ]);
  }
}