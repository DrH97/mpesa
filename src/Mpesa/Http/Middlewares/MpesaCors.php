<?php

namespace DrH\Mpesa\Http\Middlewares;

use Illuminate\Support\Facades\Response;

/**
 * Class MobileMoneyCors
 * @package DrH\Http\Middlewares
 */
class MpesaCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        header('Access-Control-Allow-Origin: *');
        $headers = [
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Auth-Token, Origin, Authorization'
        ];
        if ($request->getMethod() === 'OPTIONS') {
            return Response::make('OK', 200, $headers);
        }
        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }
        return $response;
    }
}
