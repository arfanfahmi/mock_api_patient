<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
          $table->id(); 
          $table->string('rm_number')->unique(); 
          $table->string('avatar')->nullable(); 
          $table->string('first_name');
          $table->string('last_name');

          // Gender hanya male/female
          $table->enum('gender', ['male', 'female']);

          $table->string('birth_place')->nullable();
          $table->date('birth_date')->nullable();

          $table->string('phone_number')->nullable();
          $table->string('street_address')->nullable();
          $table->string('city_address')->nullable();
          $table->string('state_address')->nullable();

          $table->string('emergency_full_name')->nullable();
          $table->string('emergency_phone_number')->nullable();

          $table->string('identity_number')->nullable();
          $table->string('bpjs_number')->nullable();

          // 'ethnic' bisa menyimpan array JSON
          $table->enum('ethnic', ['Jawa', 'Sunda', 'Batak', 'Minang', 'Bugis', 'Papua', 'Lainnya'])->nullable();

          // Education sesuai daftar yang kamu kasih
          $table->enum('education', [
              'SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4',
              'S1', 'S2', 'S3', 'Pendidikan Profesi'
          ])->nullable();

          $table->text('communication_barrier')->nullable(); 
          $table->string('disability_status')->nullable();

          // Married status pakai enum fixed
          $table->enum('married_status', ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati']);

          $table->string('father_name')->nullable();
          $table->string('mother_name')->nullable();

          // Job pakai enum bahasa Indonesia (contoh umum)
          $table->enum('job', [
              'Pelajar', 'Mahasiswa', 'Pegawai Negeri', 'Pegawai Swasta', 
              'Wiraswasta', 'Petani', 'Nelayan', 'Buruh', 
              'Ibu Rumah Tangga', 'Tidak Bekerja', 'Pensiunan', 'Lainnya'
          ])->nullable();

          // Tambahan blood type sesuai permintaan
          $table->enum('blood_type', ['A', 'B', 'O', 'AB'])->nullable();

          $table->timestamps();
      });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
