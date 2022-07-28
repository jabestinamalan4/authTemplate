<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\Hash;

trait HelperTrait {

    public function encryptData($content)
    {
        $key=env('ENCRYPTION_KEY');
		$iv=env('ENCRYPTION_IV');
        if (gettype($content) == 'string') {
            $encrypted = base64_encode(openssl_encrypt($content, 'AES-256-CBC', $key, OPENSSL_RAW_DATA,$iv));
        }
        else{
            $encrypted = base64_encode(openssl_encrypt(json_encode($content), 'AES-256-CBC', $key, OPENSSL_RAW_DATA,$iv));
        }

        return $encrypted;

    }

    public function checkUser($user,$request)
    {
        if($request->googleUser == 0 && Hash::check($request->password, $user->password)){
            return $user;
        }
        elseif($request->googleUser == 1){
            return $user;
        }
        else{
            $response = ['status' => false, "message"=> ['These credentials do not match our records.']];
            $encryptedResponse['data'] = $this->encryptData($response);
            return $encryptedResponse;
        }
    }
}