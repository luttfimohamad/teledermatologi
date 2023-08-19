<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function index()
    {
        // $doctor = Doctor::all();
        // return response()->json($doctor);
        return response()->json(['user' => Auth::guard('apiadmin')->user()], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required'],
            'password' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $validator->errors(),
                'status' => '422'
            ]);
        }

        if (Auth::guard('webadmin')->attempt($request->only('email', 'password'))) {
            /** @var \App\Models\Admin $user **/
            $user = Auth::guard('webadmin')->user();

            // $token = $user->createToken('Admin')->accessToken;
            $token = $user->createToken($user->name)->plainTextToken;

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

    public function add_admin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'gender' => ['required'],
            'phone' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $validator->errors()
            ], 422);
        }

        $admin = new Admin([
            'name' => $request->name,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' =>  bcrypt($request->password)
        ]);
        $admin->save();

        return response()->json([
            'success' => true,
            'message' => 'Successfully created user!',
            'user' => $admin
        ], 200);
    }

    public function edit_admin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'gender' => ['required'],
            'phone' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $validator->errors()
            ], 422);
        }

        $id = Auth::user()->id;
        $admin = Admin::find($id);

        $admin->update([
            'name' => $request->name,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' =>  bcrypt($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated user!',
            'user' => $admin
        ], 200);
    }

    public function edit_patient(Request $request, $patient_email)
    {
        $patient = Patient::where('email', $patient_email)->first();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'gender' => ['required'],
            'phone' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $validator->errors(),
                'status' => '422'
            ]);
        }

        $patient->update([
            'name' => $request->name,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' =>  bcrypt($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated user!',
            'user' => $patient
        ], 200);
    }

    public function delete_patient(Request $request, $patient_email)
    {
        $patient = Patient::where('email', $patient_email);
        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted user!',
            'user' => $patient
        ], 200);
    }

    public function add_doctor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'gender' => ['required'],
            'phone' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $validator->errors(),
                'status' => '422'
            ], 422);
        }

        $doctor = new Doctor([
            'name' => $request->name,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' =>  bcrypt($request->password)
        ]);
        $doctor->save();

        return response()->json([
            'success' => true,
            'message' => 'Successfully created user!',
            'status' => '200',
            'user' => $doctor
        ], 200);
    }

    public function edit_doctor(Request $request, $doctor_email)
    {
        $doctor = Doctor::where('email', $doctor_email)->first();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'gender' => ['required'],
            'phone' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $validator->errors()
            ], 422);
        }

        $doctor->update([
            'name' => $request->name,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' =>  bcrypt($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated user!',
            'user' => $doctor
        ], 200);
    }

    public function delete_doctor(Request $request, $doctor_email)
    {
        $doctor = Doctor::where('email', $doctor_email);
        $doctor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted user!',
            'user' => $doctor
        ], 200);
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\Admin $user **/
        $user = Auth::guard('apiadmin')->user();
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
