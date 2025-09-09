<?php

namespace App\Providers;

use App\Models\Pegawai;
use App\Models\Jabatan;
use App\Models\Disposisi;
use App\Http\Controllers\ApprovalCutiController;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProviderx;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Providers\PegawaiProvider;


class AuthServiceProvider extends ServiceProviderx
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('isSuperAdmin', function($user) {
            return $user->role->is_superadmin == 1;
        });

        Gate::define('isKeuangan', function($user) {
            return $user->role->role_name == 'keuangan' ? 1 : 0;
        });

        Gate::define('isTimPengadaan', function($user){
            return in_array($user->role->role_name, ['diklat', 'investasi', 'rutin']);
        });
        
        Gate::define('isPl', function($user){
            return $user->role->role_name == 'pl' ? 1 : 0;
        });

       
      //   Auth::provider('pegawais', function ($app, array $config) {
      //     return new PegawaiProvider($app['hash'], $config['model']);
      // });
    }
}
