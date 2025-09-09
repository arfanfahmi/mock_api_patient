<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rm_number' => $this->faker->unique()->numerify('######'),
            'avatar' => 'https://api.dicebear.com/6.x/identicon/svg?seed=' . fake()->unique()->userName,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),

            'gender' => $this->faker->randomElement(['male', 'female']),

            'birth_place' => $this->faker->city(),
            'birth_date' => $this->faker->date(),

            'phone_number' => $this->faker->phoneNumber(),
            'street_address' => $this->faker->streetAddress(),
            'city_address' => $this->faker->city(),
            'state_address' => $this->faker->state(),

            'emergency_full_name' => $this->faker->name(),
            'emergency_phone_number' => $this->faker->phoneNumber(),

            'identity_number' => $this->faker->unique()->numerify('################'),
            'bpjs_number' => $this->faker->optional()->numerify('#############'),

            'ethnic' => $this->faker->randomElement(['Jawa', 'Sunda', 'Batak', 'Minang', 'Bugis', 'Papua', 'Lainnya']),

            'education' => $this->faker->randomElement([
                'SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4',
                'S1', 'S2', 'S3', 'Pendidikan Profesi'
            ]),

            'communication_barrier' => null,
            'disability_status' => null,

            'married_status' => $this->faker->randomElement(['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati']),

            'father_name' => $this->faker->name('male'),
            'mother_name' => $this->faker->name('female'),

            'job' => $this->faker->randomElement([
                'Pelajar', 'Mahasiswa', 'Pegawai Negeri', 'Pegawai Swasta',
                'Wiraswasta', 'Petani', 'Nelayan', 'Buruh',
                'Ibu Rumah Tangga', 'Tidak Bekerja', 'Pensiunan', 'Lainnya'
            ]),

            'blood_type' => $this->faker->optional()->randomElement(['A', 'B', 'O', 'AB']),
        ];
    }
}
