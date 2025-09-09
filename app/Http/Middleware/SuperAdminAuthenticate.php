<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperadminAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
          
          $user = Auth::guard('admin')->user();

          $isSuperAdmin = Role::where('is_superadmin', 1)
            ->whereHas('user', function ($query) use ($user) {
                $query->where('id', $user->id);
            })
            ->exists();

            if ($isSuperAdmin == 1) {
                return $next($request);
            }
        }
        
        return redirect('ramah/login');
    }
}
