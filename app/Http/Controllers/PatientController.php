<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'gender' => ['required'],
            'phone' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validation Error',
                    'error' => $validator->errors(),
                    'status' => '422'
                ],
            );
        }

        $patient = new Patient([
            'name' => $request->name,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' =>  bcrypt($request->password)
        ]);

        $patient->save();

        return response()->json([
            'success' => true,
            'message' => 'Successfully created patient!',
            'status' => '200',
            'patient' => $patient
        ], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
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

        if (Auth::attempt($request->only('email', 'password'))) {
            /** @var \App\Models\Patient $user **/
            $user = Auth::user();

            $token = $user->createToken('Patient')->accessToken;

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged in!',
                'status' => '200',
                'patient' => $user,
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

    public function get_all_patients()
    {
        $patients = Patient::all();
        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved all patients!',
            'status' => '200',
            'patient' => $patients
        ], 200);
    }

    public function get_a_singgle_patient($id)
    {
        $patient = Patient::find($id);
        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved patient!',
            'status' => '200',
            'patient' => $patient
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'gender' => ['required'],
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

        $patient = Patient::where('email', Auth::user()->email)->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found',
                'status' => 404,
            ], 404);
        }

        $patient->update([
            'name' => $request->name,
            'gender' => $request->gender,
            'phone' => $request->phone,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated patient!',
            'status' => 200,
            'patient' => $patient,
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

        $patient = Patient::where('email', Auth::user()->email)->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found',
                'status' => 404,
            ], 404);
        }

        $patient->update([
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
        /** @var \App\Models\Patient $user **/
        $user = Auth::guard('apipatient')->user();

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
