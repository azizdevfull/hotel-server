<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\User;
use Illuminate\Http\Request;
use mrmuminov\eskizuz\Eskiz;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ProfileResource;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
{
    User::whereNull('phone_verified_at')->where('created_at', '<=', now()->subMinute(5))->delete();

    $validator = Validator::make($request->all(), [
        'name' => 'nullable|string|min:3|max:255',
        'username' => 'required|string|min:3|max:255|unique:users',
        'phone' => ['required','string','unique:users'],
        'password' => 'required|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = User::create([
        'name' => $request->name,
        'username' => $request->username,
        'phone' => $request->phone,
        'password' => Hash::make($request->password),
    ]);

    $code = mt_rand(10000, 99999);

    $eskiz = new Eskiz(env('ESKIZ_EMAIL'), env('ESKIZ_SECRET'));
    $eskiz->requestAuthLogin();
    $result = $eskiz->requestSmsSend(
        '4546',
        'Sizning maxsus tasdiqlovchi kodingiz: ' .$code. PHP_EOL .' Kodni hech kimga bermang!',
        $request->phone,
        '1', // your-message-identity, a special identity to message
        ''
    );
    if ($result->getResponse()->isSuccess() == true) {
        // Save the verification code in the cache
        $key = 'phone_verification_'.$request->phone;
        Cache::put($key, $code, now()->addMinutes(5));

        // Return success response with message
        return response()->json([
            'status' => true,
            'message' => __("auth.sms_sent"),
        ], 200);
    } else {
        // Return error response with message
        return response()->json([
            'status' => false,
            'message' => __("auth.sms_failed")
        ], 500);
    }
}


    public function verifySms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255',
            'code' => 'required|string|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $key = 'phone_verification_'.$request->phone;
        $code = Cache::get($key);

        if (!$code || $request->code != $code) {
            return response()->json([
                'status' => false,
                'message' => __('auth.sms_invalid')
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();
        $user->phone_verified_at = now();
        $user->save();

        Cache::forget($key);

        // Generate API token for the authenticated user
        $token = $user->createToken('api_token')->plainTextToken;

        // Return success response with message and token
        return response()->json([
            'status' => true,
            'message' => __('auth.phone_verified'),
            'token' => $token,
            'user' => new ProfileResource($user),
        ], 200);
    }


    public function resendSms(Request $request)
{
    $validator = Validator::make($request->all(), [
        'phone' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = User::where('phone', $request->phone)->first();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => __('auth.user_not_found'),
        ], 404);
    }
    if ($user->phone_verified_at) {
        return response()->json([
            'status' => false,
            'message' => __('auth.already_verified')
        ], 403);
    }

    // Generate a new verification code
    $code = mt_rand(10000, 99999);

    $eskiz = new Eskiz(env('ESKIZ_EMAIL'), env('ESKIZ_SECRET'));
    $eskiz->requestAuthLogin();
    $result = $eskiz->requestSmsSend(
        '4546',
        'Sizning maxsus tasdiqlovchi kodingiz: ' .$code. PHP_EOL .' Kodni hech kimga bermang!',
        $request->phone,
        '1', // your-message-identity, a special identity to message
        ''
    );

    if ($result->getResponse()->isSuccess == true) {
        // Save the new verification code in the cache
        $key = 'phone_verification_'.$request->phone;
        Cache::put($key, $code, now()->addMinutes(5));

        // Return success response with message
        return response()->json([
            'status' => true,
            'message' => __('auth.sms_sent')
        ], 200);
    } else {
        // Return error response with message
        return response()->json([
            'status' => false,
            'message' => __('auth.sms_failed')
        ], 500);
    }
}


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!User::where('phone', $value)->orWhere('username', $value)->exists()) {
                        return $fail(__('auth.invalid_input'));
                    }
                },
            ],
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where(function ($query) use ($request) {
            $query->where('phone', $request->login)
                  ->orWhere('username', $request->login);
        })->first();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => __('auth.invalid_input')
            ], 401);
        }

        if (!$user->phone_verified_at) {
            return response()->json([
                'status' => false,
                'message' =>  __('auth.phone_not_verified')
            ], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => __('auth.login_success'),
            'token' => $token,
            'user' => new ProfileResource($user),
        ], 200);
    }

public function forgotPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'phone' => [
            'required',
            'string',
            Rule::exists('users', 'phone'),
        ],
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = User::where('phone', $request->phone)->first();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => __('auth.phone_not_found')
        ], 404);
    }

    // $code = mt_rand(10000, 99999);

    // $key = 'reset_password_'.$request->email;
    // Cache::put($key, $code, now()->addMinutes(5));



    $code = mt_rand(10000, 99999);

    $eskiz = new Eskiz(env('ESKIZ_EMAIL'), env('ESKIZ_SECRET'));
    $eskiz->requestAuthLogin();
    $result = $eskiz->requestSmsSend(
        '4546',
        'Your Reset code: '.$code,
        $request->phone,
        '1', // your-message-identity, a special identity to message
        ''
    );
    if ($result->getResponse()->isSuccess == true) {
        // Save the verification code in the cache
        $key = 'reset_password_'.$request->phone;
        Cache::put($key, $code, now()->addMinutes(5));

        // Return success response with message
        return response()->json([
            'status' => true,
            'message' => __('auth.sms_sent')
        ], 200);
    } else {
        // Return error response with message
        return response()->json([
            'status' => false,
            'message' => __('auth.sms_failed')
        ], 500);
    }



}


public function resetPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'phone' => [
            'required',
            'string',
            Rule::exists('users', 'phone'),
        ],
        'code' => 'required|string|max:5',
        'password' => 'required|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $key = 'reset_password_'.$request->phone;
    $code = Cache::get($key);

    if (!$code || $request->code != $code) {
        return response()->json([
            'status' => false,
            'message' => __('auth.invalid_code')
        ], 422);
    }

    $user = User::where('phone', $request->phone)->first();
    $user->password = Hash::make($request->password);
    $user->save();

    Cache::forget($key);

    // Generate API token for the authenticated user
    // $token = $user->createToken('api_token')->plainTextToken;

    // Return success response with message and token
    return response()->json([
        'status' => true,
        'message' => __('auth.reset_success'),
        // 'token' => $token,
        // 'user' => $user
    ], 200);
}


public function logoutUser(Request $request)
{
$accessToken = $request->bearerToken();


$token = PersonalAccessToken::findToken($accessToken);


$token->delete();

return response()->json([
    'status' => true,
    'message' => __('auth.logout'),
], 200);

}


}
