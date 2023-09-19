<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Chapter;
use App\Models\Lesson;

class LessonController extends Controller
{
    //
    public function index(Request $request) {
        $lessons = Lesson::query();

        // get query params
        $chapterId = $request->query('chapter_id');

        // make fillter
        $lessons->when($chapterId, function($query) use($chapterId) {
            return $query->where('chapter_id', '=', $chapterId);
        });

        return response()->json([
            'status' => 'success',
            'data' => $lessons->get()
        ]);
    }

    public function show($id) {
        $lesson = Lesson::find($id);

        if(!$lesson) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lesson not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }

    public function create(Request $request) {
        $rules = [
            'name' => 'required|string',
            'video' => 'required|string',
            'chapter_id' => 'required|integer'
        ];

        // get all data from body
        $data = $request->all();

        // make validation
        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // cek apakah chapter id valid
        $chapterId = $request->input('chapter_id');
        $chapter = Chapter::find($chapterId);

        if(!$chapter) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chapter not found'
            ], 404);
        }

        // save to db
        $lesson = Lesson::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }

    public function update(Request $request, $id) {
        $rules = [
            'name' => 'string',
            'video' => 'string',
            'chapter_id' => 'integer'
        ];

        // get all data from body
        $data = $request->all();

        // make validation
        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ]);
        }

        // cek apakah lesson valid
        $lesson = Lesson::find($id);
        if(!$lesson) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lesson not found'
            ], 404);
        }

        $chapterId = $request->input('chapter_id');
        if($chapterId) {
            $chapter = Chapter::find($chapterId);
            if(!$chapter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Chapter Not Found'
                ], 404);
            }
        }

        $lesson->fill($data);
        $lesson->save();
        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }

    public function destroy($id) {
        $lesson = Lesson::find($id);

        if(!$lesson) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lesson Not Found'
            ], 404);
        }

        $lesson->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Lesson Deleted!'
        ]);
    }
}
