<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle($request, Closure $next){
        try {
            JWTAuth::parseToken()->authenticate();
            // echo '<pre>===========================================<br>';
            // print_r('0');
            // echo'</pre>';
            // exit;
        } catch (TokenExpiredException $e) {
            // echo '<pre>===========================================<br>';
            // print_r('1');
            // echo'</pre>';
            // exit;
            return response()->json(['error' => 'Token expirado'], 401);
        } catch (TokenInvalidException $e) {
            // echo '<pre>===========================================<br>';
            // print_r('2');
            // echo'</pre>';
            // exit;
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (JWTException $e) {
            // echo '<pre>===========================================<br>';
            // print_r('3');
            // echo'</pre>';
            // exit;
            return response()->json(['error' => 'Token ausente ou inválido'], 401);
        }

        return $next($request);
    }
    protected function unauthenticated($request, AuthenticationException $exception){
        echo '<pre>===========================================<br>';
        print_r('ss');
        echo'</pre>';
        exit;
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Não autenticado. Token ausente, inválido ou expirado.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
