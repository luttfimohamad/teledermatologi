<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Detection;
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
                    'status' => 422
                ],
                422
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
            'status' => 200,
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
                    'status' => 422
                ],
                422
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

    public function add_detection(Request $request, $patient_id)
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
            'condition' => 'required',
            'image' => 'required|max:1024'
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validation Error',
                    'error' => $validator->errors(),
                    'status' => 422
                ],
                422
            );
        }

        $filename = "";
        if ($request->hasFile('image')) {
            $filename = $request->file('image')->store('detection_images', 'public');
        } else {
            $filename = null;
        }

        $detection = Detection::create([
            'patient_id' => $patient_id,
            'condition' => $request->input('condition'),
            'image' => $filename,
        ]);

        if ($detection) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully created detection!',
                'status' => 200,
                'detection' => $detection
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create detection!',
                'status' => 500
            ], 500);
        }
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

    public function update_detection_by_patient_id(Request $request, $patient_id, $detection_id)
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

        $validator = Validator::make($request->all(), [
            'condition' => 'required',
            'image' => 'required|max:1024'
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validation Error',
                    'error' => $validator->errors(),
                    'status' => 422
                ],
                422
            );
        }

        $filename = "";
        if ($request->hasFile('image')) {
            $filename = $request->file('image')->store('detection_images', 'public');
        } else {
            $filename = null;
        }

        $detection->update([
            'condition' => $request->input('condition'),
            'image' => $filename,
        ]);

        if ($detection) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully updated detection!',
                'status' => 200,
                'detection' => $detection
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update detection!',
                'status' => 500
            ], 500);
        }
    }

    // public function update_detection(Request $request, $patient_id, $detection_id)
    // {
    //     $patient = Patient::find($patient_id);

    //     if (!$patient) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Patient not found',
    //             'status' => 404,
    //         ], 404);
    //     }

    //     $detection = Detection::find($detection_id);
    //     dd($detection);

    //     if (!$detection) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Detection not found',
    //             'status' => 404,
    //         ], 404);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'condition' => 'required',
    //         'image' => 'required|max:1024'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation Error',
    //             'error' => $validator->errors(),
    //             'status' => 422
    //         ], 422);
    //     }

    //     if ($request->has('condition')) {
    //         $detection->condition = $request->condition;
    //     }

    //     if ($request->hasFile('image')) {
    //         $filename = $request->file('image')->store('detection_images', 'public');
    //         $detection->image = $filename;
    //     }

    //     $detection->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Successfully updated detection!',
    //         'status' => 200,
    //         'detection' => $detection
    //     ], 200);

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

//     public function edit($id)
//     {
//         $images=ImageCrud::findOrFail($id);
//         return response()->json($images);
//     }

//     public function update(Request $request,$id)
//     {
//         $images=ImageCrud::findOrFail($id);

//         $destination=public_path("storage\\".$images->image);
//         $filename="";
//         if($request->hasFile('new_image')){
//             if(File::exists($destination)){
//                 File::delete($destination);
//             }

//             $filename=$request->file('new_image')->store('posts','public');
//         }else{
//             $filename=$request->image;
//         }

//         $images->title=$request->title;
//         $images->image=$filename;
//         $result=$images->save();
//         if($result){
//             return response()->json(['success'=>true]);
//         }else{
//             return response()->json(['success'=>false]);
//         }
//     }
