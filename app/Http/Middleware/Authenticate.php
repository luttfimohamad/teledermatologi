<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            if (Auth::guard('apiadmin')->check()) {
                return route('admin.dashboard');
            } elseif (Auth::guard('apidoctor')->check()) {
                return route('doctor.dashboard');
            } elseif (Auth::guard('apipatient')->check()) {
                return route('patient.dashboard');
            } else {
                return route('home');
            }
        }
    }
}
