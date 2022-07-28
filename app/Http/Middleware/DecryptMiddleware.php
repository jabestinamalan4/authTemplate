<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Traits\HelperTrait;

class DecryptMiddleware
{
    use HelperTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Uncomment this to validate whether the user email is valid

        // // if (isset(auth()->user()->id) && auth()->user()->valid == "0") {
        // //     $response = ['status' => false, "message"=> ['Please verify OTP to login.']];
        // //     $encryptedResponse['data'] = $this->encryptData($response);
        // //     return response($encryptedResponse, 200);
        // // }

        $key=env('ENCRYPTION_KEY');
        $iv=env('ENCRYPTION_IV');

        if (isset($request->input)) {
            $value=openssl_decrypt(base64_decode($request->input),'AES-256-CBC',$key,OPENSSL_RAW_DATA,$iv);
            if(json_decode($value) != null){
                $input = json_decode($value);
            }
            else{
                $input = $value;
            }

            if($request->has('input')) {
                $request->merge(['input' => $input]);
            }

            if (isset($input->page)) {
                $request->merge(['page' => $input->page]);
            }
        }

        return $next($request);
    }
}
