<?php

namespace App\Http\Controllers;

use App\Models\Mentor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MentorController extends Controller
{

    public function index() {
        $mentors = Mentor::all();

        return response()->json([
            'status' => 'success',
            'data' => $mentors
        ]);
    }

    public function show($id) {
        $mentor = Mentor::find($id);

        if(!$mentor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mentor Not Found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $mentor
        ]);
    }

    public function create(Request $request) {
        // make rules
        $rules = [
            'name' => 'required|string',
            'email' => 'required|email',
            'profile' => 'required|url',
            'profession' => 'required|string'
        ];

        // get all data from body
        $data = $request->all();

        // validasi data
        $validator = Validator($data, $rules);

        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // add data to DB
        $mentor = Mentor::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $mentor
        ]);
    }

    public function update(Request $request, $id) {
        // make rules
        $rules = [
            'name' => 'string',
            'email' => 'email',
            'profile' => 'url',
            'profession' => 'string'
        ];

        // get all data from body
        $data = $request->all();

        // validasi data
        $validator = Validator($data, $rules);

        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // cek data mentor
        $mentor = Mentor::find($id);

        if(!$mentor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mentor Not Found'
            ], 404);
        }

        // jika Mentor ada
        $mentor->fill($data);
        $mentor->save();

        return response()->json([
            'status' => 'success',
            'data' => $mentor
        ], 200);
    }

    public function destroy($id) {
        $mentor = Mentor::find($id);

        if(!$mentor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mentor Not Found'
            ], 404); 
        }

        // hapus data
        $mentor->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Mentor Deleted'
        ], 200);
    }
}
