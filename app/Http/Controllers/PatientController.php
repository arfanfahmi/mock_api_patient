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
        $query = Patient::query();

        // 🔍 Pencarian bebas (nama, rm_number, telepon)
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('rm_number', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // 🔍 Filter gender (enum: male, female)
        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        // 🔍 Filter blood_type (enum: A, B, O, AB)
        if ($request->filled('blood_type')) {
            $query->where('blood_type', $request->input('blood_type'));
        }

        // 🔍 Filter education (enum: SD, SMP, SMA, D1, D2, D3, D4, S1, S2, S3, Pendidikan Profesi)
        if ($request->filled('education')) {
            $query->where('education', $request->input('education'));
        }

        // 🔍 Filter status pernikahan (enum: Belum Kawin, Kawin, Cerai Hidup, Cerai Mati)
        if ($request->filled('married_status')) {
            $query->where('married_status', $request->input('married_status'));
        }

        // Pagination default: 10 per halaman
        $patients = $query->paginate(10);

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
