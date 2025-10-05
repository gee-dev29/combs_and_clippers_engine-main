<?php

namespace App\Http\Middleware;

use Closure;
//use App\Models\ApiToken;
use App\Models\Customer;


class ThirdPartyToken
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
        //return is_null($request['appid'];
        if(!is_null($request->header('Token'))){
           $app = Customer::where('merchant_code', $request->header('Token'))->first();
           if(!is_null($app)){
             return $next($request);
           } else{
             return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 401, 
                                     "ResponseMessage" => "Your credential is incorrect"]);
           }
        }else{
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 400, 
                                     "ResponseMessage" => 'You are not authorized to access this resource']);
        }
        //return $next($request);
    }
}
