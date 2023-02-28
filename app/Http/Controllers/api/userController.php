<?php

namespace App\Http\Controllers\api;
use App\Models\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Auth;
use Twilio\Rest\Client;

class userController extends Controller
{
    public function authenticateWithPhone(request $request){
        try{
            $rules = array(
                'phone' =>"required|min:6",
            );
    
            $validator = Validator::make($request->all(),$rules);
            if($validator->fails())
            {
                return response()->json([
                    "status" => 404,
                    "message" => "Validation Error",
                    "data" => $validator->errors(),
                ]);
            }
            $user = User::where('phone', $request->phone)->first();
            if($user == ""){
                User::create(['phone' => $request->phone, 'password' => bcrypt('PassWord')]);
            }

            $otp = rand(100000, 999999);
            User::where('phone', $request->phone)->update(['otp' => $otp]);

            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_TOKEN");
            $twilio_number = getenv("TWILIO_FROM");
  
            $client = new Client($account_sid, $auth_token);
            $receiverNumber = "+91" . $request->phone;
            $client->messages->create($receiverNumber, [
                'from' => $twilio_number, 
                'body' => "Your OTP for login into Community Application is : " . $otp]);

            return response()->json([
                "status" => 200,
                "message" => "We have sent you an OTP to your registered Mobile Number",
            ]);

        }catch(\Exception $e){
            return ($e->getMessage());
        }
    }

    public function resendOtp(request $request){
        try{
            $user = User::where('phone', $request->phone)->first();
            if($user == ""){
                User::create(['phone' => $request->phone]);
            }

            $otp = rand(100000, 999999);
            User::where('phone', $request->phone)->update(['otp' => $otp]);

            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_TOKEN");
            $twilio_number = getenv("TWILIO_FROM");
  
            $client = new Client($account_sid, $auth_token);
            $receiverNumber = "+91" . $request->phone;
            $client->messages->create($receiverNumber, [
                'from' => $twilio_number, 
                'body' => "Your OTP for login into Community Application is : " . $otp]);

            return response()->json([
                "status" => 200,
                "message" => "We have sent you an OTP to your registered Mobile Number",
            ]);

        }catch(\Exception $e){
            return ($e->getMessage());
        }
    }


    public function otpVerification(request $request){
        try{
            $rules = array(
                'phone'  => "required",
                'otp'  =>"required",
            );
    
            $validator = Validator::make($request->all(),$rules);
            if($validator->fails())
            {
                return response()->json([
                    "status" => 404,
                    "message" => "Validation Error",
                    "data" => $validator->errors(),
                ]);
            }

            $phone = $request->phone;
            $otp = $request->otp;
            $userDetails = User::where('otp', $otp)->where('phone', $phone)->first();
            if($userDetails == ""){
                return response()->json([
                    "status" => 404,
                    "message" => "Your OTP is incorrect",
                ]);
            }

            Auth::login($userDetails);
            $user = Auth::user();
            return response()->json([
                "status" => 200,
                "message" => "Congratulations, You are successfully Logged In",
                "token" => $user->createToken('MyApp')->plainTextToken,
            ]);
            
        }catch(\Exception $e){
            return ($e->getMessage());
        }
    }

    public function getLoggedInUser(){
        try{
            $user = Auth::user();
            return response()->json([
                "status" => 200,
                "message" => "Success",
                "data" => $user,
            ]);
        }catch(\Exception $e){
            return ($e->getMessage());
        }
    }

    public function updateProfile(request $request, $id){
        try{
            $rules = array(
                'firstname'  =>"required",
                'lastname'  =>"required",
                'username'  =>"required",
                'email' => 'unique:users,email'
            );
    
            $validator = Validator::make($request->all(),$rules);
            if($validator->fails())
            {
                return response()->json([
                    "status" => 404,
                    "message" => "Validation Error",
                    "data" => $validator->errors(),
                ]);
            }

            $existing_user = User::find($id);
            if($existing_user == ""){
                return response()->json([
                    "status" => 404,
                    "message" => "User ID is incorrect",
                    "data" => ["user_id" => $id],
                ]);
            }
            // Profile Pic
            if($request->file('profilePic')) {
                $file = $request->file('profilePic');
                $profilePic_filename = time().'_'.$file->getClientOriginalName();
                // File extension
                $extension = $file->getClientOriginalExtension();
                // File upload location
                $location = 'images/profilePic';
                // Upload file
                $file->move($location,$profilePic_filename);
            }else{
                $profilePic_filename = $existing_user->profilePic;
            }

            // Profile Cover
            if($request->file('profileCover')) {
                $file = $request->file('profileCover');
                $profileCover_filename = time().'_'.$file->getClientOriginalName();
                // File extension
                $extension = $file->getClientOriginalExtension();
                // File upload location
                $location = 'images/profileCover';
                // Upload file
                $file->move($location,$profileCover_filename);
            }else{
                $profileCover_filename = $existing_user->profileCover;
            }

            $user = [
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'username' => $request->username,
                'profilePic' => $profilePic_filename,
                'profileCover' => $profileCover_filename,
                'bio' => $request->bio,
                'phone' => $request->phone,
                'isVerified' => $request->isVerified,
                'otp' => $request->otp,
                'socialMedia' => $request->socialMedia,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ];

            User::find($id)->update($user);
            return response()->json([
                "status" => 200,
                "message" => "Congratulations, You are successfully registered now",
                "data" => $user,
            ]);
        }catch(\Exception $e){
            return ($e->getMessage());
        }
    }
}
