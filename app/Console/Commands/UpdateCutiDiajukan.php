<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Cuti;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Mail;
use App\Mail\PembatalanCutiOtomatisMail;

class UpdateCutiDiajukan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:cutiDiajukan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status cuti yang belum disetujui pada pukul 07:00:00 menjadi ditolak';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $cutis = Cuti::where('status', 'Diajukan')->get();
        
        if (count($cutis) >= 1) {
            foreach($cutis as $cuti) {
                $tglMulai = Carbon::parse($cuti->tgl_mulai)->format('Y-m-d'). ' 07.00.00';
                $responHariH = Carbon::parse($tglMulai);
                $createdAt = Carbon::parse($cuti->created_at);
                
                if ($createdAt->copy()->addDay(1) > $responHariH && $createdAt < $responHariH) {
                    $batasRespon = $responHariH;
                } else {
                    $batasRespon = $createdAt->copy()->addDay(1);
                }

                if (Carbon::now() > $batasRespon) {
                    $cuti->status = 'Dibatalkan';
                    $cuti->alasan_tolak = 'Tidak ada respon dari atasan sampai batas waktu persetujuan cuti.';
                    $cuti->save();

                    /*
                    * Kirim email pemberitahuan ke bawahan
                    */
                    $cutiAfterApproval = Cuti::join('jenis_cutis', 'cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
                        ->where('cutis.id', $cuti->id)
                        ->select('cutis.*', 'nama_jenis_cuti')
                        ->first();

                    $pegawai = Pegawai::where('id', $cuti->pegawai_id)->first();

                    Mail::to($pegawai->email)->send(new PembatalanCutiOtomatisMail($pegawai, $cutiAfterApproval));
                }
            }
        }

        $this->info(Carbon::now()->format('H.i'). ' - Set batal cuti otomatis OK');
    }
}
