<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Middleware\CheckLessonEnrollmentMiddleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\View\View;

class SubmissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(CheckLessonEnrollmentMiddleware::class, only: ['checkAndStartExamAttempt', 'submitExamAttempt', 'show'])
            //!
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function checkAndStartExamAttempt(Request $request, $lessonId, $examId)
    {
        $submitted = Submission::where('enroll_id', $request->get('enrolled_id'))->where('exam_id', $examId)->get();
        // echo $submitted;
        // echo $request->get('enrolled_id');
        // echo $request->input('enrolled_id');

        if ($submitted->isNotEmpty()) {
            return redirect()->route('submissions.show', [
                'lessonId' => $lessonId,
                'examId' => $examId,
                'submissionId' => $submitted->last()->id
            ]);
        }

        return $this->startExamAttempt($lessonId, $request->get('enrolled_id'), $examId);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function startExamAttempt($lessonId, $enrolledId, $examId): View
    {
        if (Cache::has('exam_' . $examId)) {
            $exam = Cache::get('exam_' . $examId);
            $duration = $exam['exam']['duration'];
            $questions = $exam['questions'];
        } else {
            $duration = Exam::where('id', $examId)->select('duration')->first()->duration;
            $exam = Exam::findOrFail($examId);
            $questions = Question::with(['options' => function ($query) {
                $query->select('id', 'question_id', 'option_text');
            }])
                ->where('exam_id', $examId)
                ->select('id', 'question_text')
                ->get();

            Cache::put('exam_' . $examId, ['exam' => $exam, 'questions' => $questions]);
        }

        $submission = Submission::create([
            'enroll_id' => $enrolledId,
            'exam_id' => $examId,
            // 'user_id' => Auth::id(),
        ]);

        return view('exams.showAttemptForm', [
            'questions' => $questions,
            'lessonId' => $lessonId,
            'examId' => $examId,
            'duration' => $duration,
            'submissionId' => $submission->id
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function submitExamAttempt(Request $request, $lessonId, $examId, $submissionId): RedirectResponse
    {
        // $questions = Question::where('exam_id', $examId)->get();
        $questions = Question::with(['options' => function ($query) {
            $query->select('id', 'question_id')->where('is_correct', true);
        }])
            ->where('exam_id', $examId)
            ->select('id')
            ->get();

        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'exists:options,id',
        ]);

        // echo $questions;
        // echo $request->input("answers.1");
        $score = 0;
        foreach ($questions as $question) {
            // echo $question->options->first()->id;
            if (!$request->has("answers.$question->id")) {
                continue;
            }
            Answer::create([
                'submission_id' => $submissionId,
                'question_id' => $question->id,
                'selected_option_id' => $request->input("answers.$question->id"),
            ]);
            if ($request->input("answers.$question->id") == $question->options->first()->id) {
                $score++;
            }
        }

        $submission = Submission::find($submissionId);
        $exam = Exam::find($examId);
        $score = ($score / count($questions)) * $exam->total_score;
        $submission->score = $score;
        $submission->save();

        // echo $score;
        // return;
        return redirect()->route('submissions.show', [
            'lessonId' => $lessonId,
            'examId' => $examId,
            'submissionId' => $submissionId
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($lessonId, $examId, $submissionId): View
    {
        return view('exams.showSubmission', [
            'lessonId' => $lessonId,
            'examId' => $examId,
            'submissionId' => $submissionId,
        ]);
    }

    public function getData($lessonId, $examId, $submissionId): JsonResponse
    {
        $submission = Submission::with(['answers' => function ($query) {
            $query->select('question_id', 'selected_option_id', 'submission_id', 'id');
        }])
            ->where('id', $submissionId)
            ->select('id', 'score', 'created_at')
            ->first();

        $questions = Question::with(['options' => function ($query) {
            $query->select('id', 'question_id', 'option_text', 'is_correct');
        }])
            ->where('exam_id', $examId)
            ->select('id', 'question_text')
            ->get();

        $exam = Exam::where('id', $examId)->select('title', 'passing_grade')->first();

        return response()->json([
            'data' => [
                'lessonId' => $lessonId,
                'examId' => $examId,
                'submissionId' => $submissionId,
                'exam' => $exam,
                'submission' => $submission,
                'questions' => $questions
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Submission $submission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Submission $submission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Submission $submission)
    {
        //
    }
}
