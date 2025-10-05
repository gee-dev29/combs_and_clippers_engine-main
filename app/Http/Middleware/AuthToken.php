<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
//use App\Models\ApiToken;
use Exception;

class AuthToken
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
        
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(["ResponseStatus" => "Unsuccessful", "message" => "Invalid or expired or missing token", "ResponseCode" => 401, "ResponseMessage" => "Invalid or expired or missing token"], 401);
            }
        } catch (Exception $e) {
         return response()->json(["ResponseStatus" => "Unsuccessful", "message" => "Invalid or expired or no token", "ResponseCode" => 401, "ResponseMessage" => $e], 401);
        }
        
        return $next($request);
    }
}