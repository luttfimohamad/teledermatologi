<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
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
                'doctor' => $user,
                'token' => $token
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'status' => '401'
            ]);
        }
    }

    public function get_all_doctors()
    {
        $doctors = Doctor::all();
        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved all doctors!',
            'status' => '200',
            'doctor' => $doctors
        ], 200);
    }

    public function get_a_singgle_doctor($id)
    {
        $doctor = Doctor::find($id);
        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved a doctor!',
            'status' => '200',
            'doctor' => $doctor
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'phone' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'status' => 422,
                'error' => $validator->errors(),
            ], 422);
        }

        $doctor = Doctor::find(Auth::user()->id);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found',
                'status' => 404
            ], 404);
        }

        $doctor->update([
            'name' => $request->name,
            'phone' => $request->phone
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated doctor!',
            'status' => 200,
            'doctor' => $doctor
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
                'status' => 422,
                'error' => $validator->errors(),
            ], 422);
        }

        $doctor = Doctor::find(Auth::user()->id);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found',
                'status' => 404,
            ], 404);
        }

        $doctor->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password successfully updated!',
            'status' => 200,
        ], 200);
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\Doctor $user **/
        $user = Auth::guard('apidoctor')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
                'status' => 401,
            ], 401);
        }

        $accessToken = $user->token();
        $accessTokenId = $accessToken->id;

        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessTokenId)
            ->update(['revoked' => true]);

        $accessToken->revoke();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'status' => 200
        ]);
    }
}
