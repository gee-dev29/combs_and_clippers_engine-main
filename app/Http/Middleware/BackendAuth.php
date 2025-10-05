<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BackendAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if((Auth::check() && Auth::user()->status == 1) && (Auth::user()->account_type == "admin" || Auth::user()->account_type == 'super_admin'))
          return $next($request);
        
        Session::put('oldUrl', $request->url());
        return redirect()->route('escrow.login');
    }
}
