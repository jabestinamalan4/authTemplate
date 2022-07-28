<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\HelperTrait;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Jobs\SendEmailJob;

class RegisterController extends Controller
{
    use HelperTrait;

    public function register(Request $request)
    {
        if (gettype($request->input) == 'array') {
            $inputData = (object) $request->input;
        }
        else{
            $inputData = $request->input;
        }


        $rulesArray = [
                        'name' => 'required|max:55',
                        'email' => 'email|required|unique:users',
                        'googleUser' => 'required'
                    ];
        if (isset($inputData->googleUser) && $inputData->googleUser == 0) {
            $rulesArray['password'] = 'required';
        }

        // you can customize the required field like this - (uncomment to use)

        // // $rulesArray['phone'] = 'required|unique:users';
        // // $rulesArray['gender'] = 'required';

        $validatedData = Validator::make((array)$inputData, $rulesArray);

        if($validatedData->fails()) {
            $error = $validatedData->errors();
            $response = ['status' => false, "message"=> [$error->first()]];
            $encryptedResponse['data'] = $this->encryptData($response);
            return response($encryptedResponse, 200);
        }

        if ($inputData->googleUser == 0) {
            $inputData->password = Hash::make($inputData->password);
        }
        else{
            $inputData->password = null;
        }

        $user = User::create((array)$inputData);

        $otp = random_int(100000, 999999);
        $user->otp = $otp;
        $user->valid = ($inputData->googleUser == 1) ? 1 : 0;

        $user->google_user = $inputData->googleUser;

        $user->save();

        //encryption
        $user_id = Crypt::encryptString($user->id);

        if ($user->google_user == 1) {
            $accessToken = $user->createToken('authToken')->accessToken;
            $response['response']['accessToken'] = $accessToken;
        }
        else{
            //send otp for email
            dispatch(new SendEmailJob($user));
        }

        $response['status'] = true;
        $response["message"] = ["User Registered Successfully"];
        $response['response']["userId"] = $user_id;

        $encryptedResponse['data'] = $this->encryptData($response);

        return response($encryptedResponse, 200);

    }
}