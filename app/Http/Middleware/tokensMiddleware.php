<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class tokensMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->bearerToken()) {
            return response()->json(['message' => 'Treinador, faltou informar seu token'], 422);
        }
        
        if (!Auth::guard('sanctum')->user()) {
            return response()->json(['message' => 'Treinador, este token não é mais válido'], 401);
        }

        $user = Auth::guard('sanctum')->user();

        if($user){
            Auth::setUser($user);
        }   

        return $next($request);
    }
}
