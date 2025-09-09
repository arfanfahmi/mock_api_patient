<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Tampilkan daftar pasien (paginated).
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10); // default 10
        $patients = Patient::paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pasien',
            'data' => $patients
        ]);
    }

    /**
     * Simpan data pasien baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rm_number' => 'required|digits:6|unique:patients,rm_number',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'phone_number' => 'nullable|string|max:20',
            'street_address' => 'nullable|string',
            'city_address' => 'nullable|string|max:100',
            'state_address' => 'nullable|string|max:100',
            'emergency_full_name' => 'nullable|string|max:150',
            'emergency_phone_number' => 'nullable|string|max:20',
            'identity_number' => 'nullable|string|max:30',
            'bpjs_number' => 'nullable|string|max:30',
            'ethnic' => 'nullable|json',
            'education' => 'nullable|in:SD,SMP,SMA,D1,D2,D3,D4,S1,S2,S3,Pendidikan Profesi',
            'communication_barrier' => 'nullable|string',
            'disability_status' => 'nullable|string|max:100',
            'married_status' => 'required|in:Belum Kawin,Kawin,Cerai Hidup,Cerai Mati',
            'father_name' => 'nullable|string|max:150',
            'mother_name' => 'nullable|string|max:150',
            'job' => 'nullable|in:Pelajar,Mahasiswa,Pegawai Negeri,Pegawai Swasta,Wiraswasta,Petani,Nelayan,Buruh,Ibu Rumah Tangga,Tidak Bekerja,Pensiunan,Lainnya',
            'blood_type' => 'nullable|in:A,B,O,AB',
            'avatar' => 'nullable|url'
        ]);

        $patient = Patient::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pasien berhasil ditambahkan',
            'data' => $patient
        ], 201);
    }

    /**
     * Tampilkan detail pasien.
     */
    public function show($id)
    {
      $patient = Patient::find($id);

      if (!$patient) {
        return response()->json([
          'success' => false,
          'message' => 'Pasien tidak ditemukan',
          'data' => null
        ], 404);
      }
        return response()->json([
            'success' => true,
            'message' => 'Detail pasien',
            'data' => $patient
        ]);
    }

    /**
     * Update data pasien.
     */
    public function update(Request $request, $id)
    {
      $patient = Patient::find($id);

      if (!$patient) {
        return response()->json([
          'success' => false,
          'message' => 'Pasien tidak ditemukan',
          'data' => null
        ], 404);
      }

        $validated = $request->validate([
            'rm_number' => 'sometimes|digits:6|unique:patients,rm_number,' . $patient->id,
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'gender' => 'sometimes|in:male,female',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'phone_number' => 'nullable|string|max:20',
            'street_address' => 'nullable|string',
            'city_address' => 'nullable|string|max:100',
            'state_address' => 'nullable|string|max:100',
            'emergency_full_name' => 'nullable|string|max:150',
            'emergency_phone_number' => 'nullable|string|max:20',
            'identity_number' => 'nullable|string|max:30',
            'bpjs_number' => 'nullable|string|max:30',
            'ethnic' => 'nullable|json',
            'education' => 'nullable|in:SD,SMP,SMA,D1,D2,D3,D4,S1,S2,S3,Pendidikan Profesi',
            'communication_barrier' => 'nullable|string',
            'disability_status' => 'nullable|string|max:100',
            'married_status' => 'sometimes|in:Belum Kawin,Kawin,Cerai Hidup,Cerai Mati',
            'father_name' => 'nullable|string|max:150',
            'mother_name' => 'nullable|string|max:150',
            'job' => 'nullable|in:Pelajar,Mahasiswa,Pegawai Negeri,Pegawai Swasta,Wiraswasta,Petani,Nelayan,Buruh,Ibu Rumah Tangga,Tidak Bekerja,Pensiunan,Lainnya',
            'blood_type' => 'nullable|in:A,B,O,AB',
            'avatar' => 'nullable|url'
        ]);

        $patient->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data pasien berhasil diupdate',
            'data' => $patient
        ]);
    }

    /**
     * Hapus pasien.
     */
    public function destroy($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Pasien tidak ditemukan',
                'data' => null
            ], 404);
        }

        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pasien berhasil dihapus'
        ]);
    }
}
