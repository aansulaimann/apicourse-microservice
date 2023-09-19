<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use App\Models\ImageCourse;

class ImageCourseController extends Controller
{
    //
    public function create(Request $request) {
        $rules = [
            'image' => 'required|url',
            'course_id' => 'required|integer'
        ];

        // get all data
        $data = $request->all();

        // make validation
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
                'message' => 'Course Not Found!'
            ], 404);
        }

        $imageCourse = ImageCourse::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $imageCourse
        ]);
    }

    public function destroy($id) {
        $image = ImageCourse::find($id);

        if(!$image) {
            return response()->json([
                'status' => 'error',
                'message' => 'Image Course Not Found!'
            ], 404);
        }

        $image->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Image Course Deleted!'
        ]);
    }
}
