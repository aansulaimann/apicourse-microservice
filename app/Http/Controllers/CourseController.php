<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use App\Models\Mentor;
use App\Models\MyCourse;
use App\Models\Review;
use App\Models\Chapter;

class CourseController extends Controller
{
    public function index(Request $request) {
        $courses = Course::query();

        // get params
        $q = $request->query('q');
        $status = $request->query('status');

        // get data from query
        $courses->when($q, function($query) use ($q) {
            return $query->whereRaw("name LIKE '%". strtolower($q) ."%'");
        });

        $courses->when($status, function($query) use ($status) {
            return $query->where('status', '=', strtolower($status));
        });

        return response()->json([
            'status' => 'success',
            'data' => $courses->paginate(10)
        ]);
    }

    public function show($id) {
        $course = Course::with('chapters.lessons')
            ->with('mentor')
            ->with('images')
            ->find($id);

        if(!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'Course not found!'
            ], 404);
        }

        // get all Review from tb review
        $reviews = Review::where('course_id', '=', $id)->get()->toArray();

        // get detail users
        if(count($reviews) > 0) {
            // ambil user id dari reviews
            $userIds = array_column($reviews, 'user_id');

            // call helpers untuk access service user
            $users = getUserByIds($userIds);

            // cek datanya
            // echo "<pre>". print_r($users, 1) ."</pre>";

            // jika service users unavailable kirimkan data array kosong
            if($users['status'] === 'error') {
                $reviews = [];
            } else {
                // jika service user tersedia, maka kirim data users dari service users berdasarkan reviews users pada course
                foreach($reviews as $key => $review) {
                    // ambil index data users dari service users, berdasarkan index users pada data reviews
                    $userIndex = array_search($review['user_id'], array_column($users['data'], 'id'));

                    // jika sudah ada indexnya, inject data tersebut ke dalam data reviews
                    $reviews[$key]['users'] = $users['data'][$userIndex];
                }
            }

        }

        $totalStudents = MyCourse::where('course_id', '=', $id)->count();

        // make data total videos
        $totalVideos = Chapter::where('course_id', '=', $id)->withCount('lessons')->get()->toArray();
        
        // cek datanya
        // echo "<pre>". print_r($totalVideos, 1) ."</pre>";

        // make total videos
        $finalTotalVideos = array_sum(array_column($totalVideos, 'lessons_count'));

        // inject data to course
        $course['reviews'] = $reviews;
        $course['total_videos'] = $finalTotalVideos;
        $course['total_students'] = $totalStudents;

        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }

    public function create(Request $request) {
        $rules = [
            'name' => 'required|string',
            'certificate' => 'required|boolean',
            'thumbnail' => 'url',
            'type' => 'required|in:free,premium',
            'status' => 'required|in:draft,published',
            'price' => 'integer',
            'level' => 'required|in:all-level,beginner,intermediate,advance',
            'mentor_id' => 'required|integer',
            'description' => 'string'
        ];

        $data = $request->all();

        $validate = Validator::make($data, $rules);

        if($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        $mentorId = $request->input('mentor_id');
        $mentor = Mentor::find($mentorId);

        if(!$mentor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mentor not found!'
            ], 404);
        }

        $course = Course::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }

    public function update(Request $request, $id) {
        $rules = [
            'name' => 'string',
            'certificate' => 'boolean',
            'thumbnail' => 'url',
            'type' => 'in:free,premium',
            'status' => 'in:draft,published',
            'price' => 'integer',
            'level' => 'in:all-level,beginner,intermediate,advance',
            'mentor_id' => 'integer',
            'description' => 'string'
        ];

        $data = $request->all();

        $validate = Validator::make($data, $rules);

        if($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        $course = Course::find($id);
        if(!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'Course Not Found'
            ], 404);
        }

        $mentorId = $request->input('mentor_id');
        if($mentorId) {
            $mentor = Mentor::find($mentorId);
            if(!$mentor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mentor not found!'
                ], 404);
            }
        }

        $course->fill($data);
        $course->save();

        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }

    public function destroy($id) {
        $course = Course::find($id);

        if(!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'Course not found!'
            ], 404);
        }

        $course->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Course Deleted'
        ]); 
    }
}
