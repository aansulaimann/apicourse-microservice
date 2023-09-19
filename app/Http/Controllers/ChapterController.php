<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;

class ChapterController extends Controller
{
    //
    public function index(Request $request) {
        $chapters = Chapter::query();

        // get params
        $courseId = $request->input('course_id');

        // make fillter
        $chapters->when($courseId, function($query) use($courseId) {
            return $query->where('course_id', '=', $courseId);
        });

        return response()->json([
            'status' => 'success',
            'data' => $chapters->get()
        ]);
    }

    public function show($id) {
        $chapter = Chapter::find($id);

        if(!$chapter) {
            return response()->json([
                'status' => 'error',
                'message' => 'chapter not found'
            ], 404); 
        }

        return response()->json([
            'status' => 'success',
            'data' => $chapter
        ]);
    }

    public function create(Request $request) {
        $rules = [
            'name' => 'required|string',
            'course_id' => 'required|integer'
        ];

        // get all data from body
        $data = $request->all();

        // validate
        $validator = Validator::make($data, $rules);

        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 404); 
        }

        // cek apakah course id valid
        $course_id = $request->input('course_id');
        $course = Course::find($course_id);

        if(!$course) {
            return response()->json([
                'error' => 'error',
                'message' => 'Course Not Found'
            ], 404);
        }

        $chapter = Chapter::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $chapter
        ]);
    }

    public function update(Request $request, $id) {
        $rules = [
            'name' => 'string',
            'course_id' => 'integer'
        ];

        // get all data from body
        $data = $request->all();

        // validate
        $validator = Validator::make($data, $rules);

        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 404); 
        }

        $chapter = Chapter::find($id);

        if(!$chapter) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chapter Not Found'
            ], 404);
        }

        $courseId = $request->input('course_id');
        if($courseId) {
            $course = Course::find($courseId);

            if(!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course Not Found'
                ], 404);
            }
        }

        $chapter->fill($data);
        $chapter->save();

        return response()->json([
            'status' => 'success',
            'data' => $chapter
        ]);
    }

    public function destroy($id) {
        $chapter = Chapter::find($id);

        if(!$chapter) {
            return response()->json([
                'status' => 'error',
                'message' => 'chapter not found'
            ], 404); 
        }

        $chapter->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'chapter deleted'
        ]);
    }
}
