<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DoctorController extends Controller
{
    public function index()
    {
        $patient = Patient::all();
        return response()->json($patient);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validation Error',
                    'error' => $validator->errors(),
                    'status' => '422'
                ]
            );
        }

        if (Auth::guard('webdoctor')->attempt($request->only('email', 'password'))) {
            /** @var \App\Models\Doctor $user **/
            $user = Auth::guard('webdoctor')->user();

            $token = $user->createToken('Doctor')->accessToken;

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged in!',
                'status' => '200',
                'token' => $token,
                'data' => $user
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'status' => '401'
            ]);
        }
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'phone' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $validator->errors(),
                'status' => '422'
            ]);
        }

        $doctor = Doctor::find(Auth::user()->id);
        $doctor->update([
            'name' => $request->name,
            'phone' => $request->phone
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated user!',
            'user' => $doctor
        ], 200);
    }

    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $validator->errors(),
                'status' => '422'
            ]);
        }

        $doctor = Doctor::find(Auth::user()->id);
        $doctor->update([
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated user!',
            'user' => $doctor
        ], 200);
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\Doctor $user **/
        $user = Auth::guard('apidoctor')->user();
        $accessToken = $user->token();
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update(['revoked' => true]);
        $accessToken->revoke();

        return response()->json([
            'success' => true,
            'message' => 'Log Out Successful',
            'status' => 200,
            'data' => 'Unauthorized'
        ]);
    }
}
