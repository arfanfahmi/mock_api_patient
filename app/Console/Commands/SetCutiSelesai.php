<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Cuti;

class SetCutiSelesai extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:setCutiSelesai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status cuti menjadi selesai';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $cutis = Cuti::where('status', 'Disetujui')->get();
        
        if (count($cutis) >= 1) {
            foreach($cutis as $cuti) {
                $tglSelesai = Carbon::parse($cuti->tgl_selesai);

                if (Carbon::now() > $tglSelesai) {
                    $cuti->status = 'Selesai';
                    $cuti->save();
                }
            }
        }

        $this->info(Carbon::now()->format('H.i'). ' - Set cuti selesai OK');
    }
}
