<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Jobs\UserRegistration;
use App\Mail\UserRegistrationMail;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mail;

class UserRegistrationApiController extends Controller
{
    // public function sendRegistrationOtp(Request $request)
    // {
    //     try {
    //         $otp = rand(100000, 999999);
    //         $currenttime = date('Y-m-d h:i:s');
    //         $otptime = strtotime($currenttime.' + 5 minute');
    //         $otptime = date('Y-m-d h:i:s', $otptime);
    //         $otphash = \Hash::make($otp);

    //         $password = $request->password;
    //         $password = Hash::make($password);

    //         $role = Role::where('rolename','User')->first();
    //         $request['role_id'] =  $role->id;

    //         $registrationexists = User::where('email', $request->email)->where('is_otp_validated', 0)->first();

    //         DB::beginTransaction();
    //         if ($registrationexists) {
    //             $users = User::where('email', $request->email)
    //                 ->update([
    //                     'salutation' => $request->salutation,
    //                     'name' => $request->name,
    //                     'lastname' => $request->lastname,
    //                     'gender' => $request->gender,
    //                     'email' => $request->email,
    //                     'password' => $password,
    //                     'role_id' => $request->role_id,
    //                     'is_otp_validated'=>0,
    //                     'otp_valid_until'=> $otptime,
    //                     'otp'=>$otphash,
    //                     'status'=>0,
    //                 ]);

    //         } else {
    //             $userexists = User::where('email', $request->email)->first();

    //             if ($userexists) {
    //                 return response()->json(['status' => 'E', 'message' => trans('returnmessage.email_already_exists')]);
    //             }
    //             $users = User::create([
    //                 'salutation' => $request->salutation,
    //                 'name' => $request->name,
    //                 'lastname' => $request->lastname,
    //                 'gender' => $request->gender,
    //                 'email' => $request->email,
    //                 'password' => $password,
    //                 'role_id' => $request->role_id,
    //                 'is_otp_validated'=>0,
    //                 'otp_valid_until'=> $otptime,
    //                 'otp'=>$otphash,
    //                 'status'=>0,
    //             ]);
    //         }
    //         $emailTemplate = EmailTemplate::where('template_name', 'OTP Verification')->first();
    //             if (isset($emailTemplate)) {
    //                 $actionText = null;
    //                 $actionUrl = null;
    //                 $userdata = ['firstname' => $request->name, 'email' => $request->email, 'otp' => $otp];
    //                 $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
    //                 $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
    //                 $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);
    //                 Mail::to($request->email)->send(new UserRegistration($parsedSubject, $parsedContent, $paresedSignature, $actionText, $actionUrl));
    //             }
    //         DB::commit();
    //         return response()->json(['status' => 'S', 'message' => trans('returnmessage.registration_mail_sent')]);
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
    //     }
    // }

    public function userRegistration(Request $request)
    {
        try {
            // $otp = mt_rand(100000, 999999);
            // $currenttime = date('Y-m-d h:i:s');
            // $otptime = strtotime($currenttime . ' + 5 minute');
            // $otptime = date('Y-m-d h:i:s', $otptime);
            // $otphash = \Hash::make($otp);

            $registrationexists = User::where('email', $request->email)->where('mobile',$request->mobile)->exists();
            // $password = $request->password;
            // $password = Hash::make($password);
            // ->where('mobile',$request->mobile)
            if ($registrationexists) {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.user_already_exists')]);
               

            } else {
         
                $users = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'mobile' => $request->mobile,
                    // 'password' => $password,
                    'is_consumer' => $request->is_consumer,
                    'status' => 1,
                ]);
            }
            $user_data=User::latest()->first();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.registration_success') ,'user_data'=>$user_data]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }
  
}
