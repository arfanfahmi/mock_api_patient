<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Config;
use App\Models\Presensi;
use Illuminate\Console\Command;

class UpdatePresensiPulang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:presensiPulang';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update presensi pulang bagi yang tidak presensi pulang';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $config = Config::first();
        $presensis = Presensi::where('flag', 'Masuk')->get();
        
        if (count($presensis) >= 1) {
            foreach($presensis as $presensi) {
                $batasPresensiPulang = Carbon::parse($presensi->jadwal_out)->addMinutes($config->akhir_presensi_pulang);

                if (Carbon::now() > $batasPresensiPulang) {
                    $presensi->flag = 'pulang_null';
                    $presensi->save();
                }
            }
        }

        $this->info(Carbon::now()->format('H.i'). ' - Set presensi pulang OK');
    }
}
