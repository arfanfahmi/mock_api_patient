<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tahun;
use Carbon\Carbon;

class CheckAllowInput
{
    public function handle($request, Closure $next)
    {
      $tahunId = $request->route('tahunId'); // Assuming you are passing tahunId as a route parameter

      if ($tahunId === null) {
          $selectedYear = Tahun::where('set_aktif', 1)->first();
      } else {
          $selectedYear = Tahun::find($tahunId);

          if (!$selectedYear) {
            abort(403, 'DATA TAHUN TIDAK DITEMUKAN');
          }
      }

      $isAllowInput = (Carbon::now() >= Carbon::parse($selectedYear->tgl_mulai_input) && Carbon::now() <= Carbon::parse($selectedYear->tgl_akhir_input)) || $selectedYear->boleh_input == 1;

      if ($isAllowInput) {
          return $next($request);
      } else {
          // Redirect or handle the error as needed
          abort(403, 'BUKAN PERIODE INPUT RKA');
      }
    }
}
