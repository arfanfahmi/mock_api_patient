<?php

// app/Traits/ApprovalTrait.php

namespace App\Traits;

use App\Models\Unit;
use App\Models\RolePrimaryUser;
use App\Models\KategoriAnggaran;
use App\Models\User;
use App\Mail\ApprovalMail;
use App\Mail\HasilApprovalMail;
use App\Mail\TanggapanRevisiMail;
use App\Mail\PermintaanRevisiMail;
use App\Mail\PermintaanPencairanAnggaranMail;
use App\Mail\PerintahPelaksanaanMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Traits\WhatsAppTrait;

trait ApprovalTrait
{
  use WhatsAppTrait;

  function setApproverAndSendApprovalNotification($receiver, $disposisi, $realisasi, $nilaiDiajukan) {
    $unitPengusul = $realisasi->unit;
    $noHpPenerima = '';
    $emailPenerima = '';

    if ($receiver == 'manager') {
      $approverDisposisi = Unit::where('unit_id', $unitPengusul->parent_unit)->first();
      
      $response = Http::get('https://sipres.rspkuwonosobo.id/api/pegawai/'. $approverDisposisi->pegawai_uuid);

      if ($response->successful()) {
        $data  = $response->json();
        $noHpPenerima = $data['data']['no_hp'];
        $emailPenerima = $data['data']['email'];
      }

      //update disposisi
      $disposisi->update(['approver_unit_id' => $approverDisposisi->id]);

      //kirim notifikasi WA
      $response = $this->sendApprovalNotificationForUser($realisasi->id, $noHpPenerima, $nilaiDiajukan);

      if ($response !== false) {
          $response = json_decode($response);
          $wamid = $response->messages[0]->id;
          $disposisi->update(['wamid' => $wamid]);
      }

      //kirim email
      Mail::to($emailPenerima)->send(new ApprovalMail($realisasi, $unitPengusul, $nilaiDiajukan, 'pegawai'));
    } else if ($receiver == 'direksi') {
      $approverDisposisi = null;

      if (checkFinalApprover($nilaiDiajukan) == 'wadir_umum') { //f in realization helper
        $approverDisposisi = Unit::where('is_wadir_umum', 1)->first();
      } else if (checkFinalApprover($nilaiDiajukan) == 'direktur') {
        $approverDisposisi = Unit::where('level_unit', 1)->first(); //level unit 1 = direktur
      }
      
      $response = Http::get('https://sipres.rspkuwonosobo.id/api/pegawai/'. $approverDisposisi->pegawai_uuid);

      if ($response->successful()) {
        $data  = $response->json();
        $noHpPenerima = $data['data']['no_hp'];
        $emailPenerima = $data['data']['email'];
      }

      //update disposisi
      $disposisi->update(['approver_unit_id' => $approverDisposisi->id]);

      //kirim notifikasi WA
      $response = $this->sendApprovalNotificationForUser($realisasi->id, $noHpPenerima, $nilaiDiajukan);

      if ($response !== false) {
          $response = json_decode($response);
          $wamid = $response->messages[0]->id;
          $disposisi->update(['wamid' => $wamid]);
      }
      //kirim email
      Mail::to($emailPenerima)->send(new ApprovalMail($realisasi, $unitPengusul, $nilaiDiajukan, 'pegawai'));
    } else if ($receiver == 'tim pengadaan') {
      $kategoriAnggaranRealisasi = KategoriAnggaran::where('id', $realisasi->kategori_anggaran_id)->first();
      $primaryUser = RolePrimaryUser::where('role_id', $kategoriAnggaranRealisasi->role_id)->first();
      $approverDisposisi = User::where('id', $primaryUser->user_id)->first();

      //update disposisi
      $disposisi->update(['approver_admin_id' => $approverDisposisi->id]); 

      //kirim email
      Mail::to($approverDisposisi->email)->send(new ApprovalMail($realisasi, $unitPengusul, $nilaiDiajukan, 'admin'));
    } else if ($receiver == 'keuangan') {
      $primaryUser = RolePrimaryUser::where('role_id', 5)->first(); //5 = keuangan
      $approverDisposisi = User::where('id', $primaryUser->user_id)->first();

      //update disposisi
      $disposisi->update(['approver_admin_id' => $approverDisposisi->id]);

      //kirim email
      Mail::to($approverDisposisi->email)->send(new ApprovalMail($realisasi, $unitPengusul, $nilaiDiajukan, 'admin'));
    }
  }

  /*
   * Pemberitahuan permintaan pencairan
   */
  function kirimNotifikasiPencairanAnggaran($realisasi, $nilaiDiajukan) {
    $unitPengusul = $realisasi->unit;

    //penerima email
    $primaryUser = RolePrimaryUser::where('role_id', 5)->first(); //5 = keuangan
    $approver = User::where('id', $primaryUser->user_id)->first();

    //kirim email
    Mail::to($approver->email)->send(new PermintaanPencairanAnggaranMail($realisasi, $unitPengusul, $nilaiDiajukan, 'admin'));
  }
  
  /*
   * Pemberitahuan pelaksanaan anggaran
   */
  function kirimNotifikasiPelaksanaan($receiver, $realisasi, $nilaiDiajukan) {
    $unitPengusul = $realisasi->unit;
    $emailPenerima = '';

    if ($receiver == 'unit') {
      $approver = Unit::where('id', $unitPengusul->id)->first();
      
      $response = Http::get('https://sipres.rspkuwonosobo.id/api/pegawai/'. $approver->pegawai_uuid);

      if ($response->successful()) {
        $data  = $response->json();
        $emailPenerima = $data['data']['email'];
      }

      //kirim email
      Mail::to($emailPenerima)->send(new PerintahPelaksanaanMail($realisasi, $unitPengusul, $nilaiDiajukan, 'pegawai'));
    } else if ($receiver == 'tim pengadaan') {
      $kategoriAnggaranRealisasi = KategoriAnggaran::where('id', $realisasi->kategori_anggaran_id)->first();
      $primaryUser = RolePrimaryUser::where('role_id', $kategoriAnggaranRealisasi->role_id)->first();
      $approver= User::where('id', $primaryUser->user_id)->first();

      //kirim email
      Mail::to($approver->email)->send(new PerintahPelaksanaanMail($realisasi, $unitPengusul, $nilaiDiajukan, 'admin'));
    } else if ($receiver == 'keuangan') {
      // $primaryUser = RolePrimaryUser::where('role_id', 5)->first(); //5 = keuangan
      // $approver = User::where('id', $primaryUser->user_id)->first();

      // //kirim email
      // Mail::to($approver->email)->send(new PerintahPelaksanaanMail($realisasi, $unitPengusul, $nilaiDiajukan, 'admin'));
      //untuk keuangan tidak perlu dikirim email
    }
  }

  /* 
  * Pemberitahuan hasil approval FINAL (disetujui/ditolak)
  */
  function sendFinalApprovalResult($realisasi, $nilaiDiajukan) {
    $unitPengusul = $realisasi->unit;
    $emailPenerima = '';

    $approverDisposisi = $unitPengusul;
    
    $response = Http::get('https://sipres.rspkuwonosobo.id/api/pegawai/'. $approverDisposisi->pegawai_uuid);

    if ($response->successful()) {
      $data  = $response->json();
      $emailPenerima = $data['data']['email'];
    }

    //kirim email
    Mail::to($emailPenerima)->send(new HasilApprovalMail($realisasi, $unitPengusul, $nilaiDiajukan, 'pegawai'));
  }
  
  function sendRevisionNotification($receiver, $realisasi, $nilaiDiajukan) {
    $unitPengusul = $realisasi->unit;
    $emailPenerima = '';

    if ($receiver == 'unit') {
      $approver = Unit::where('id', $unitPengusul->id)->first();
      
      $response = Http::get('https://sipres.rspkuwonosobo.id/api/pegawai/'. $approver->pegawai_uuid);

      if ($response->successful()) {
        $data  = $response->json();
        $emailPenerima = $data['data']['email'];
      }

      //kirim email
      Mail::to($emailPenerima)->send(new PermintaanRevisiMail($realisasi, $unitPengusul, $nilaiDiajukan, 'pegawai'));
    } else if ($receiver == 'tim pengadaan') {
      $kategoriAnggaranRealisasi = KategoriAnggaran::where('id', $realisasi->kategori_anggaran_id)->first();
      $primaryUser = RolePrimaryUser::where('role_id', $kategoriAnggaranRealisasi->role_id)->first();
      $approver= User::where('id', $primaryUser->user_id)->first();

      //kirim email
      Mail::to($approver->email)->send(new PermintaanRevisiMail($realisasi, $unitPengusul, $nilaiDiajukan, 'admin'));
    } else if ($receiver == 'keuangan') {
      $primaryUser = RolePrimaryUser::where('role_id', 5)->first(); //5 = keuangan
      $approver = User::where('id', $primaryUser->user_id)->first();

      //kirim email
      Mail::to($approver->email)->send(new PermintaanRevisiMail($realisasi, $unitPengusul, $nilaiDiajukan, 'admin'));
    }
  }
  
  /*
   * Email pemberitahuan bahwa revisi telah ditanggapi
   */
  function sendRevisionResponseNotification($disposisi, $realisasi, $nilaiDiajukan) {
    $unitPengusul = $realisasi->unit;
    $emailPenerima = '';

    //jika pengirim revisi (penerima disposisi terakhir) adalah manager/direksi
    if ($disposisi->approver_unit_id !== null) {
      $unitApprover = Unit::where('id', $disposisi->approver_unit_id)->first();
      
      $response = Http::get('https://sipres.rspkuwonosobo.id/api/pegawai/'. $unitApprover->pegawai_uuid);

      if ($response->successful()) {
        $data  = $response->json();
        $noHpPenerima = $data['data']['no_hp'];
        $emailPenerima = $data['data']['email'];
      }

      if ($unitApprover->level_unit == 1 || $unitApprover->level_unit == 2) { //direksi, kirim via WA

        //kirim notifikasi WA
        $response = $this->sendApprovalNotificationForUser($realisasi->id, $noHpPenerima, $nilaiDiajukan);

        if ($response !== false) {
            $response = json_decode($response);
            $wamid = $response->messages[0]->id;
            $disposisi->update(['wamid' => $wamid]);
        }
        
      } else { //manager, kirim via email
       Mail::to($emailPenerima)->send(new TanggapanRevisiMail($realisasi, $unitPengusul, $nilaiDiajukan, 'pegawai'));
      }
    } else {
      $approver= User::where('id', $disposisi->approver_admin_id)->first();

      //kirim email
      Mail::to($approver->email)->send(new TanggapanRevisiMail($realisasi, $unitPengusul, $nilaiDiajukan, 'admin'));
    }
  }
}
