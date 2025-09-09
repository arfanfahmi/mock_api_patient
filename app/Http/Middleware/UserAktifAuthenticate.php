<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UserAktifAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $pegawai = Pegawai::where('id', Auth::user()->id)->first();

        if (!auth()->guest() && $pegawai->aktif == 1) {
            return $next($request);
        }

        return redirect()->route('login.logout')->with('error', 'User tidak aktif');
    }
}
