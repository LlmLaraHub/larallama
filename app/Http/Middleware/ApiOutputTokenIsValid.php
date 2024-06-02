<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiOutputTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $token = $request->output->fromMetaData('token');

        $tokenPassedIn = $request->input('token');

        if ($request->bearerToken()) {
            $tokenPassedIn = $request->bearerToken();
        }

        Log::info('ApiOutput Request', [
            '$tokenPassedIn' => $tokenPassedIn,
            '$token' => $token,
        ]);

        if ($tokenPassedIn !== $token) {
            abort(404);
        }

        return $next($request);
    }
}
