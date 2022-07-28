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

    public function otp_verification(Request $request){

        if (gettype($request->input) == 'array') {
            $inputData = (object) $request->input;
        }
        else{
            $inputData = $request->input;
        }

        $rulesArray = [
                        'otp' => 'max:6|required',
                        'userId' => 'required'
                    ];
        $validatedData = Validator::make((array)$inputData, $rulesArray);

        if($validatedData->fails()) {
            $response = ['status' => false, "message"=> [$validatedData->errors()->first()]];
            $encryptedResponse['data'] = $this->encryptData($response);
            return response($encryptedResponse, 200);
        }

        $status = true;
        $code = 200;
        $message = ["OTP Verified Successfully"];

        //decryption
        $user_id = Crypt::decryptString($inputData->userId);

        $data = User::where('id',$user_id)->first();
        if (isset($data->id)) {
            if($data->otp == $inputData->otp && $data->valid != 1){

                User::where('id', $user_id)->update(array('valid' => 1));

                $accessToken = $data->createToken('authToken')->accessToken;
                $response['response']['userId'] = $inputData->userId;
                $response['response']['accessToken'] = $accessToken;
            }
            elseif($data->valid == 1){
                $status = false;
                $code = 200;
                $message = ["User is already Verified"];
            }
            else{
                $status = false;
                $code = 200;
                $message = ["OTP is Invalid"];
            }
        }
        else{
            $status = false;
            $code = 200;
            $message = ["User Id is Invalid"];
        }

        $response['status'] = $status;
        $response["message"] = $message;

        $encryptedResponse['data'] = $this->encryptData($response);

        return response($encryptedResponse, $code);

    }

}