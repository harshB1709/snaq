<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Detection\MobileDetect;

class EnsureDeviceIsAllowed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $event = $request->route('event', null);
        $app_setting = $event->appSettings()->where([
            'key' => 'force_mobile'
        ])->first();

        $detect = new MobileDetect();
        $is_mobile = $detect->isMobile();

        if(($app_setting?->value ?? true) && !$is_mobile && !$request->user() && config('app.env') === 'production')
            return abort(400, $app_setting?->message ?? "Please use a mobile phone to play the game!");
        return $next($request);
    }
}
