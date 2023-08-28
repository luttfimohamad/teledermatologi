<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Detection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
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

            $token = $user->createToken('Admin')->accessToken;

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged in!',
                'status' => '200',
                'admin' => $user,
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

    public function get_all_admin()
    {
        $admin = Admin::all();
        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved all admins!',
            'status' => '200',
            'admin' => $admin
        ], 200);
    }

    public function get_a_singgle_admin($id)
    {
        $admin = Admin::find($id);
        if ($admin) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved a admin!',
                'status' => '200',
                'admin' => $admin
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found!',
                'status' => '404'
            ], 404);
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
                'status' => '422',
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
            'message' => 'Successfully created admin!',
            'status' => '200',
            'admin' => $admin
        ], 200);
    }

    public function update_admin(Request $request, $admin_id)
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
                'status' => 422,
                'error' => $validator->errors()
            ], 422);
        }

        $admin = Admin::find($admin_id);

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found',
                'status' => 404,
            ], 404);
        }

        $admin->update([
            'name' => $request->name,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated admin!',
            'status' => 200,
            'admin' => $admin
        ], 200);
    }

    public function delete_admin($admin_id)
    {
        $admin = Admin::find($admin_id);

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found',
                'status' => 404,
            ], 404);
        }

        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted admin!',
            'status' => 200,
            'admin' => $admin
        ], 200);
    }

    public function add_doctor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'gender' => ['required'],
            'specialization' => ['required'],
            'phone' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'status' => '422',
                'error' => $validator->errors(),
            ], 422);
        }

        $doctor = new Doctor([
            'name' => $request->name,
            'gender' => $request->gender,
            'specialization' => $request->specialization,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' =>  bcrypt($request->password)
        ]);
        $doctor->save();

        return response()->json([
            'success' => true,
            'message' => 'Successfully created doctor!',
            'status' => '200',
            'doctor' => $doctor
        ], 200);
    }

    public function update_doctor(Request $request, $doctor_id)
    {
        $doctor = Doctor::find($doctor_id);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found',
                'status' => 404,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'gender' => ['required'],
            'specialization' => ['required'],
            'phone' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'status' => '422',
                'error' => $validator->errors(),
            ], 422);
        }

        $doctor->update([
            'name' => $request->name,
            'gender' => $request->gender,
            'specialization' => $request->specialization,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated doctor!',
            'status' => '200',
            'doctor' => $doctor
        ], 200);
    }

    public function delete_doctor(Request $request, $doctor_id)
    {
        $doctor = Doctor::find($doctor_id);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found',
                'status' => 404,
            ], 404);
        }

        $doctor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted doctor!',
            'status' => "200",
            'doctor' => $doctor,
        ], 200);
    }

    public function update_patient(Request $request, $patient_id)
    {
        $patient = Patient::find($patient_id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found',
                'status' => 404,
            ], 404);
        }

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
                'status' => 422
            ], 422);
        }

        $patient->update([
            'name' => $request->name,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated patient!',
            'status' => 200,
            'patient' => $patient
        ], 200);
    }

    public function delete_patient(Request $request, $patient_id)
    {
        $patient = Patient::find($patient_id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'patient not found',
                'status' => 404,
            ], 404);
        }

        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted patient!',
            'status' => 200,
            'patient' => $patient,
        ], 200);
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\Admin $user **/
        $user = Auth::guard('apiadmin')->user();

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

    public function get_all_doctors()
    {
        $doctors = Doctor::all();
        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved all doctors!',
            'status' => 200,
            'doctors' => $doctors
        ], 200);
    }

    public function get_a_singgle_doctor($id)
    {
        $doctor = Doctor::find($id);
        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved a doctor!',
            'status' => 200,
            'doctor' => $doctor
        ], 200);
    }

    public function get_all_patients()
    {
        $patients = Patient::all();
        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved all patients!',
            'status' => 200,
            'patients' => $patients
        ], 200);
    }

    public function get_a_singgle_patient($id)
    {
        $patient = Patient::find($id);
        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved a patient!',
            'status' => 200,
            'patient' => $patient
        ], 200);
    }

    public function get_all_detections_by_patient_id($patient_id)
    {
        $patient = Patient::find($patient_id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found',
                'status' => 404,
            ], 404);
        }

        $detections = DB::table('detections')
            ->where('patient_id', $patient_id)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved all detections for patient!',
            'status' => 200,
            'detections' => $detections
        ], 200);
    }

    public function get_a_singgle_detection_by_patient_id($patient_id, $detection_id)
    {
        $patient = Patient::find($patient_id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found',
                'status' => 404,
            ], 404);
        }

        $detection = DB::table('detections')
            ->where('patient_id', $patient_id)
            ->where('id', $detection_id)
            ->first();

        if (!$detection) {
            return response()->json([
                'success' => false,
                'message' => 'Detection not found',
                'status' => 404,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved detection for patient!',
            'status' => 200,
            'detection' => $detection
        ], 200);
    }

    public function delete_detection_by_patient_id($patient_id, $detection_id)
    {
        $patient = Patient::find($patient_id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found',
                'status' => 404,
            ], 404);
        }

        $detection = Detection::find($detection_id);

        if (!$detection) {
            return response()->json([
                'success' => false,
                'message' => 'Detection not found',
                'status' => 404,
            ], 404);
        }

        $detection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted detection!',
            'status' => 200,
            'detection' => $detection
        ], 200);
    }
}
