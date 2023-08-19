<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function index()
    {
        $patient = Patient::find(Auth::user()->id);
        return response()->json($patient);
    }

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
            'message' => 'Successfully created user!',
            'status' => '200',
            'user' => $patient
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
                'token' => $token,
                'data' => $user
            ], 200);
        } else {
            return response()->json([
                'message' => 'Invalid credentials',
                'status' => '401',
            ]);
        }
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'gender' => ['required'],
            'phone' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $validator->errors(),
                'status' => '422'
            ]);
        }

        $patient = Patient::where('email', Auth::user()->email);
        $patient->update([
            'name' => $request->name,
            'gender' => $request->gender,
            'phone' => $request->phone,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated user!',
            'user' => $patient,
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

        $patient = Patient::where('email', Auth::user()->email);
        $patient->update([
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated user!',
            'user' => $patient,
        ], 200);
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\Patient $user **/
        $user = Auth::guard('apipatient')->user();
        $accessToken = $user->token();
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update(['revoked' => true]);
        $accessToken->revoke();

        return response()->json([
            'success' => true,
            'message' => 'Log Out Successful',
            'status' => 200,
            'data' => 'Unauthorized',
        ]);
    }
}
