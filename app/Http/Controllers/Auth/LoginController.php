<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use App\Http\Traits\HelperTrait;
use Illuminate\Http\Request;
use App\Models\User;

class LoginController extends Controller
{
    use HelperTrait;

    public function login(Request $request)
    {
        if (gettype($request->input) == 'array') {
            $inputData = (object) $request->input;
        }
        else{
            $inputData = $request->input;
        }

        $rulesArray = [
                        'userName' => 'required',
                        'googleUser' => 'required'
                    ];
        if (isset($inputData->googleUser) && $inputData->googleUser == 0) {
            $rulesArray['password'] = 'required';
        }
        $validatedData = Validator::make((array)$inputData, $rulesArray);

        if($validatedData->fails()) {
            $response = ['status' => false, "message"=> [$validatedData->errors()->first()]];
            $encryptedResponse['data'] = $this->encryptData($response);
            return response($encryptedResponse, 200);
        }

        $isUserExist = User::where('email',$inputData->userName)->first();

        if (isset($isUserExist->id)) {
            $user = $this->checkUser($isUserExist,$inputData);
        }

        if (isset($user->id)) {
            $user_id = Crypt::encryptString($user->id);

            if($user->valid == 0 && $user->google_user == 0){
                $response = ['status' => false, "message"=> ['Please verify OTP to login.']];
                $encryptedResponse['data'] = $this->encryptData($response);
                return response($encryptedResponse, 200);
            }

            // For notification login save device key
            // // if (isset($inputData->device_key)) {
            // //     $user->device_key = $inputData->device_key;
            // //     $user->save();
            // // }

            $response['status'] = true;
            $response["message"] = ["User Registered Successfully"];
            $response['response']['accessToken'] = $user->createToken('authToken')->accessToken;
            $response['response']["userId"] = $user_id;
            $response['response']["name"] = $user->name;
            $response['response']["email"] = $user->email;
            // $response['response']["phone"] = $user->phone;
            // $response['response']["gender"] = ($user->gender == 1) ? 'male' : 'female';

            $encryptedResponse['data'] = $this->encryptData($response);

            return response($encryptedResponse, 200);
        }
        else{
            return response($user,200);
        }
    }
}