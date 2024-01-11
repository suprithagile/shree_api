<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Mail\UserRegistrationMail;
use App\Models\EmailTemplate;
use App\Models\User;
use Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Mail;

class RecoverPasswordApiController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Send password reset link.
     */
    public function sendPasswordResetLink(Request $request)
    {
        return $this->sendResetLinkEmail($request);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
        }

        $userdata = User::where('email', $request->email)->where('status', 1)->first();
        if (!$userdata) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.credentials_mismatch')]);
        }
        if ($request->role == 'User') {
            if ($userdata->rolename !== 'User') {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.credentials_mismatch')]);
            }
        }
        if (!$request->role) {
            if ($userdata->rolename == 'User') {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.credentials_mismatch')]);
            }
        }

        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
        ? $this->sendResetLinkResponse($request, $response)
        : $this->sendResetLinkFailedResponse($request, $response);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }
    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */

    protected function sendResetLinkResponse(Request $request, $response)
    {
        return response()->json([
            'status' => 'S',
            'message' => trans('returnmessage.password_reset_email_sent'),
            'data' => $response,
        ]);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return response()->json(['status' => 'E', 'message' => trans('returnmessage.credentials_mismatch')]);
    }

    /**
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function callResetPassword(Request $request)
    {

        return $this->reset($request);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);
        $user->save();

        event(new PasswordReset($user));
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        return response()->json(['status' => 'S', 'message' => trans('returnmessage.password_reset_success')]);
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        return response()->json(['status' => 'E', 'message' => trans('returnmessage.failed_invalid_token')]);
    }

    //reset password trait functions

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *     @param  string  $response
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => 'required|confirmed|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
        }
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
        ? $this->sendResetResponse($request, $response)
        : $this->sendResetFailedResponse($request, $response);

    }
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ];
    }

    protected function credentials(Request $request)
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
    }

    public function validateOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password_confirmation' => 'required',
            'email' => ['required', 'email', 'max:255'],
            'otp' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
        }
        try {
            $otp = \Hash::make((int) $request->otp);
            $user = User::where('email', $request->email)
                ->first();
            $currenttime = date('Y-m-d h:i:s');
            if ($user) {

                if ($currenttime > $user->otp_valid_until) {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.invalid_verification_code')]);
                }
                if (!Hash::check($request->otp, $user->otp)) {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.invalid_verification_code')]);
                } else {
                    $passwordhash = \Hash::make($request->password_confirmation);
                    $user->password = $passwordhash;
                    $user->otp = null;
                    $user->save();
                }
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.password_updated_successful')]);
            } else {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.invalid_verification_code')]);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    public function sendRegistrationOtp(Request $request)
    {
        try {
            $otp = rand(100000, 999999);
            $currenttime = date('Y-m-d h:i:s');
            $otptime = strtotime($currenttime . ' + 5 minute');
            $otptime = date('Y-m-d h:i:s', $otptime);
            $otphash = \Hash::make($otp);
            $password = $request->password;
            $password = Hash::make($password);
            $emailexist = User::where('email', $request->email)->first();

            $updateOtp = User::where('email', $emailexist->email)
                ->update(['otp' => $otphash,
                    'otp_valid_until' => $otptime]);

            $emailTemplate = EmailTemplate::where('template_name', 'Resend OTP')->first();
            if (isset($emailTemplate)) {
                $actionText = null;
                $actionUrl = null;
                $userdata = ['firstname' => $request->name, 'email' => $request->email, 'otp' => $otp];
                $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);
                Mail::to($request->email)->send(new UserRegistration($parsedSubject, $parsedContent, $paresedSignature, $actionText, $actionUrl));
            }

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.Resend_Otp')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    public function sendPasswordReset(Request $request)
    {
        try {
            $otp = rand(100000, 999999);
            $currenttime = date('Y-m-d h:i:s');
            $otptime = strtotime($currenttime . ' + 5 minute');
            $otptime = date('Y-m-d h:i:s', $otptime);
            $otphash = \Hash::make($otp);

            $updateOtp = User::where('email', $request->email)
                ->update(['otp' => $otphash,
                    'otp_valid_until' => $otptime]);

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.credentials_mismatch')]);
            }
           // $emailTemplate = EmailTemplate::where('template_name', 'Forgot Password')->first();
            // if (isset($emailTemplate)) {
                $actionText = null;
                $actionUrl = null;
                $userdata = ['firstname' => $user->name . ' ' . $user->lastname, 'otp' => $otp];
                $parsedSubject = CustomFunctions::EmailContentParser('Forget Password', $userdata);
                $parsedContent = CustomFunctions::EmailContentParser('Hi {{firstname}}, our Verification Code is {{otp}}. This code will expire in 5 minutes', $userdata);
                $paresedSignature = CustomFunctions::EmailContentParser('<p>Regards,<br />Base App</p>\n', $userdata);
                Mail::to($request->email)->send(new UserRegistrationMail($parsedSubject, $parsedContent, $paresedSignature, $actionText, $actionUrl));
            // }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.password_reset_email_sent')]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }

    }
}
