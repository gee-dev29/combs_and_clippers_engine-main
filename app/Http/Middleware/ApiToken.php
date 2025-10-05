<?php

namespace App\Http\Middleware;

use Closure;
//use App\Models\ApiToken;

class ApiToken
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
    if (!is_null($request->header('appid')) && !is_null($request->header('Token'))) {
      $app = ApiToken::where([['app_id', $request->header('appid')], ['token', $request->header('Token')]])->first();
      if (!is_null($app)) { 
        return $next($request);
      } else {
        return response()->json([
          "ResponseStatus" => "Unsuccessful",
          "ResponseCode" => 401,
          "ResponseMessage" => "Your credentials are incorrect"
        ]);
      }
    } else {
      return response()->json([
        "ResponseStatus" => "Unsuccessful",
        "ResponseCode" => 400,
        "ResponseMessage" => 'You are not authorized to access this resource'
      ]);
    }
    //return $next($request);
  }
}
