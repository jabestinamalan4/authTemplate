<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use App\Http\Traits\HelperTrait;
use Illuminate\Http\Request;
use App\Jobs\SendEmailJob;
use App\Models\User;

class PasswordController extends Controller
{
    use HelperTrait;

    public function forget_otp(Request $request)
    {
        if (gettype($request->input) == 'array') {
            $inputData = (object) $request->input;
        }
        else{
            $inputData = $request->input;
        }

        $rulesArray = [
                        'userName' => 'required'
                    ];
        $validatedData = Validator::make((array)$inputData, $rulesArray);

        if($validatedData->fails()) {
            $response = ['status' => false, "message"=> [$validatedData->errors()->first()]];
            $encryptedResponse['data'] = $this->encryptData($response);
            return response($encryptedResponse, 200);
        }

        //sending the otp here
        $otp = random_int(100000, 999999);

        //update the otp in user table
        $isUserExist = User::where('email',$inputData->userName)->first();

        if (isset($isUserExist->id)) {
            $data = $isUserExist;
        }

        if(isset($data->id)){
            $update = User::where('id', $data->id)->update(array('otp' => $otp));
            $user = User::find($data->id);

            //we need to send otp here
            dispatch(new SendEmailJob($user));

            //response
            $user_id = Crypt::encryptString($data->id);

            $response['status'] = true;
            $response["message"] = ["OTP sent successfully"];
            $response['response']["userId"] = $user_id;

            $encryptedResponse['data'] = $this->encryptData($response);

            return response($encryptedResponse, 200);
        }
        else{
            $response = ['status' => false, "message"=> ['These credentials does not match our records.']];
            $encryptedResponse['data'] = $this->encryptData($response);
            return response($encryptedResponse, 200);
        }
    }

}