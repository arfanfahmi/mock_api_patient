<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
          
          $user = Auth::guard('admin')->user();

          $isPl = Role::where(function($query) {
                    $query->where('role_name', 'pl');
                    $query->orWhere('is_superadmin', 1);
                    $query->orWhere('role_name', 'keuangan');
                })
                ->whereHas('user', function ($query) use ($user) {
                    $query->where('id', $user->id);
                })
                ->exists();

          if ($isPl) {
            return $next($request);
          }
        }
        
        return redirect('ramah/login');
    }
}
