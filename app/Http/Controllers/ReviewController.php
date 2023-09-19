<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Review;
use App\Models\Course;

class ReviewController extends Controller
{
    public function create(Request $request) {
        $rules = [
            'user_id' => 'required|integer',
            'course_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'note' => 'string'
        ];

        // get all data from body
        $data = $request->all();

        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // cek course id
        $courseId = $request->input('course_id');
        $course = Course::find($courseId);

        if(!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'Course Not Found'
            ], 404);
        }

        // cek user id
        $userId = $request->input('user_id');
        $user = getUser($userId);

        if($user['status'] === 'error') {
            return response()->json([
                'status' => $user['status'],
                'message' => $user['message']
            ], $user['http_code']);
        }

        // cek apakah user pernah review sebelumnya
        $isExistsReview = Review::where('course_id', '=', $courseId)->where('user_id', '=', $userId)->exists();
        // jika duplikat
        if($isExistsReview) {
            return response()->json([
                'status' => 'error',
                'message' => 'Review already exists'
            ], 409);
        }

        // save to db
        $review = Review::create($data);
        return response()->json([
            'status' => 'success',
            'data' => $review
        ]);
    }

    public function update(Request $request, $id) {
        $rules = [
            'rating' => 'integer|min:1|max:5',
            'note' => 'string'
        ];

        // ambil data kecuali course dan user id
        $data = $request->except('user_id', 'course_id');

        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // cek apakah review ditemukan
        $review = Review::find($id);
        if(!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'Review Not Found'
            ], 404);
        }

        // update to db
        $review->fill($data);
        $review->save();

        return response()->json([
            'status' => 'success',
            'data' => $review
        ]);
    }

    public function destroy($id) {
        // cek apakah review ditemukan
        $review = Review::find($id);
        if(!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'Review Not Found'
            ], 404);
        }

        $review->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Review Deleted'
        ]);
    }
}
