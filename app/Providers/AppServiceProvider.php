<?php

namespace App\Providers;

use App\Models\KategoriAnggaran;
use App\Models\Tahun;
use App\Models\Realisasi;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
      config(['app.locale'=>'id']);
      Carbon::setLocale('id');

      //global array untuk nge-link komponen remun dengan tabel
      $globalArray = [
          'key1' => 'value1',
          'key2' => 'value2',
          // ... add more key-value pairs as needed
      ];
  
      view()->share('globalArray', $globalArray);      

      View::composer(['layouts.partials.sidebar', 'dashboard'], function($view) 
        {
          //jumlah pending approval
          $unitDipimpin = getUnitDipimpin(); 
          $jmlPendingApprovalUnit = DB::table('approval_units')
              ->whereIn('parent_unit_id', $unitDipimpin)
              ->where('status_approval', 'menunggu_approval')
              ->count();

          $jmlPendingApprovalManajer = DB::table('approval_manajers')
              ->whereIn('manajer_unit_id', $unitDipimpin)
              ->where('status_approval', 'menunggu_approval')
              ->count();

          $view->with(
              'jmlPendingApproval', 
              ($jmlPendingApprovalManajer + $jmlPendingApprovalUnit)
          );
      });
      
    }
}
