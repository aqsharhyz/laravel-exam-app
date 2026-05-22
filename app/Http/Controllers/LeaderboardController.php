<?php

namespace App\Http\Controllers;

use App\Models\Enroll;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LeaderboardController extends Controller
{
    public function index() {}

    public function examLeaderboard($exam_id)
    {
        Exam::findOrFail($exam_id);
        $leaderboard = Cache::get('exam_leaderboard_' . $exam_id);

        if (!$leaderboard) {
            $this->makeExamLeaderboardCache($exam_id);
            $leaderboard = Cache::get('exam_leaderboard_' . $exam_id);
        }

        return response()->json($leaderboard);
    }

    public function lessonLeaderboard($lesson_id)
    {
        Lesson::findOrFail($lesson_id);
        $leaderboard = Cache::get('lesson_leaderboard_' . $lesson_id);

        if (!$leaderboard) {
            $this->makeLessonLeaderboardCache($lesson_id);
            $leaderboard = Cache::get('lesson_leaderboard_' . $lesson_id);
        }

        return response()->json($leaderboard);
    }

    function makeExamLeaderboardCache($exam_id)
    {
        $leaderboard = Submission::where('exam_id', $exam_id)
            ->orderBy('score', 'desc')
            ->select('id', 'score', 'enroll_id')
            ->with(['enroll' => function ($query) {
                $query->select('id', 'user_id');
                $query->with(['user' => function ($query) {
                    $query->select('id', 'name');
                }]);
            }])
            ->get()
            ->map(function ($submission) {
                return [
                    'score' => $submission->score,
                    'user_name' => $submission->enroll->user->name,
                ];
            });

        $leaderboard = [
            'leaderboard' => $leaderboard,
            'max_score' => Exam::find($exam_id)->total_score,
            'last_updated' => now()->toDateTimeString(),
            'message' => 'Updated every 15 minutes',
        ];

        Cache::put('exam_leaderboard_' . $exam_id, $leaderboard, 900);
    }

    function makeLessonLeaderboardCache($lesson_id)
    {
        $leaderboard = Enroll::where('lesson_id', $lesson_id)
            ->with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
            ->with(['submissions' => function ($query) {
                $query->select('id', 'score', 'enroll_id');
            }])
            ->get()
            ->map(function ($enroll) {
                return [
                    'user_name' => $enroll->user->name,
                    'score' => $enroll->submissions->sum('score'),
                ];
            });

        $leaderboard = [
            'leaderboard' => $leaderboard->sortByDesc('score')->values(),
            'max_score' => Lesson::find($lesson_id)->exams->sum('total_score'),
            'last_updated' => now()->toDateTimeString(),
            'message' => 'Updated every hour',
        ];

        Cache::put('lesson_leaderboard_' . $lesson_id, $leaderboard, 3600);
    }
}
