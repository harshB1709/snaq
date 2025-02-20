<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppSetting
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $setting_key): Response
    {
        $event = $request->route('event', null);
        $app_setting = $event->appSettings()->where([
            'key' => $setting_key
        ])->first();

        $app_setting_val = $app_setting?->value ?? true;

        $user = $request->user();

        if(($event->is_active && $app_setting_val) || $user)
            return $next($request);

        $message = $app_setting?->message ?? 'The game is not available as of now. Please try again later';

        return abort(503, $message);
    }
}
