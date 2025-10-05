<?php

namespace App\Http\Controllers\App;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        $user = User::where('token', $request->token)->first();

        if (is_null($user)) {
            return $this->errorResponse('Invalid token supplied', 400);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse('Email already verified', 400);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            $this->Mailer->sendAccountCreationEmail($user);
            $this->Mailer->sendWelcomeEmail($user);
        }

        return response()->json(["message" => "Email verified successfully."], 200);
    }

    public function resendVerification(Request $request)
    {
        $user = $this->getAuthUser($request);
        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse('Email already verified', 400);
        }
        $user->update(['token' => Str::random(64)]);
        $this->Mailer->sendVerificationEmail($user);
        return response()->json(["message" => "Email verification link has been sent to your email"]);
    }

    public function sendSupportEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }
        try {
            $name = $request->name;
            $email = $request->email;
            $subject = $request->subject;
            $message = $request->message;
            $this->Mailer->sendSupportEmail($name, $email, $subject, $message);
            return response()->json(["message" => "Email has been sent successfully"]);
        } catch (Exception $e) {
            $this->reportExceptionOnBugsnag($e);
            return response()->json(["ResponseStatus" => "Unsuccessful", "ResponseCode" => 500, 'Detail' => $e->getMessage(), 'message' => 'Something went wrong', "ResponseMessage" => 'Something went wrong'], 500);
        }
    }
}
