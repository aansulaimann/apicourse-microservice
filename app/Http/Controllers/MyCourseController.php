<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\MyCourse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MyCourseController extends Controller
{
    public function index(Request $request) {
        $mycourses = MyCourse::query()->with('course');

        $userId = $request->query('user_id');

        // make fillter
        $mycourses->when($userId, function($query) use ($userId) {
            return $query->where('user_id', '=', $userId);
        });

        return response()->json([
            'status' => 'success',
            'data' => $mycourses->get()
        ]);
    }

    public function create(Request $request) {
        $rules = [
            'course_id' => 'required|integer',
            'user_id' => 'required|integer'
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $courseId = $request->input('course_id');
        $course = Course::find($courseId);

        if(!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'Course not found'
            ], 404);
        }

        // get user from service user
        $userId = $request->input('user_id');

        $user = getUser($userId);
        if($user['status'] === 'error') {
            return response()->json([
                'status' => $user['status'],
                'message' => $user['message']
            ], $user['http_code']);
        }

        // cek apakah data duplikat atau tidak
        $isExistMyCourse = MyCourse::where('course_id', '=', $courseId)
                                    ->where('user_id', '=', $userId)
                                    ->exists();
        if($isExistMyCourse) {
            return response()->json([
                'status' => 'error',
                'message' => 'User already taken'
            ], 409);
        }

        // validasi apakah course yang dibeli premium atau free
        if($course->type === 'premium') {

            // cek apakah ada harganya atau tidak
            if($course->price === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Price Can\'t be 0'
                ], 405);
            }

            // jika premium
            // echo "<pre>".print_r($user['data'], 1)."</pre>";
            // echo "<pre>".print_r($course->toArray(), 1)."</pre>"; die;
            $order = postOrder([
                // data for order
                'user' => $user['data'],
                'course' => $course->toArray()
            ]);

            // debug
            // echo "<pre>".print_r($order, 1)."</pre>";

            // jika order gagal
            if($order['status'] === 'error') {
                return response()->json([
                    'status' => $order['status'],
                    'message' => $order['message']
                ], $order['http_code']);
            }

            // jika order berhasil
            return response()->json([
                'status' => $order['status'],
                'data' => $order['data']
            ], $order['http_code']);
        } else {
            // jika free
            // simpan ke DB
            $mycourse = MyCourse::create($data);
            return response()->json([
                'status' => 'success',
                'data' => $mycourse
            ]);
        }

    }

    public function createPremiumAccess(Request $request) {
        $data = $request->all();

        // create data to db
        $myCourse = MyCourse::create($data);

        // return response
        return response()->json([
            'status' => 'success',
            'data' => $myCourse
        ]);
    }
}
