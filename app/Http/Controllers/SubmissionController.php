<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Submission;
use App\Models\Option;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Http\Middleware\CheckLessonEnrollmentMiddleware;
use App\Jobs\SubmitSubmissionJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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
        $submitted = Submission::where('enroll_id', $request->get('enrolled_id'))
            ->where('exam_id', $examId)
            ->where('is_submitted', true)
            ->get();

        if ($submitted->isNotEmpty()) {
            return redirect()->route('submissions.show', [
                'lessonId' => $lessonId,
                'examId' => $examId,
                'submissionId' => $submitted->last()->id
            ]);
        }

        $submission = Submission::where('enroll_id', $request->get('enrolled_id'))
            ->where('exam_id', $examId)
            ->where('is_submitted', false)
            ->get();

        if ($submission->isEmpty()) {
            return $this->startExamAttempt($lessonId, $request->get('enrolled_id'), $examId);
        }

        $this->checkIfTimeIsUp($submission->last());

        return $this->startExamAttempt($lessonId, $request->get('enrolled_id'), $examId, $submission->last());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function startExamAttempt($lessonId, $enrolledId, $examId, $submission = null): View
    {
        // $authorizeAttempt = Gate::inspect('attempt', [Submission::class, $lessonId, $request->get('enrolled_id'), $examId]);

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

        if ($submission) {
            $answers = Answer::where('submission_id', $submission->id)->get();
            foreach ($questions as $question) {
                $answer = $answers->where('question_id', $question->id)->first();
                if ($answer) {
                    $question->selected_option_id = $answer->selected_option_id;
                }
            }
            $submission->load('answers');
        } else {
            $submission = Submission::create([
                'enroll_id' => $enrolledId,
                'exam_id' => $examId,
                // 'user_id' => Auth::id(),
            ]);
        }

        SubmitSubmissionJob::dispatch($examId, $submission->id)->delay(now()->addSeconds((int) $duration));

        return view('exams.showAttemptForm', [
            'questions' => $questions,
            'lessonId' => $lessonId,
            'examId' => $examId,
            'submissionId' => $submission->id,
            'submission' => $submission,
            'due' => $submission->created_at->addSeconds((int)$duration),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function submitExamAttempt(Request $request, $lessonId, $examId, $submissionId): RedirectResponse
    {
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

        $score = 0;
        foreach ($questions as $question) {
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
        $submission->is_submitted = true;
        $submission->save();

        return redirect()->route('submissions.show', [
            'lessonId' => $lessonId,
            'examId' => $examId,
            'submissionId' => $submissionId
        ]);
    }

    /**
     * Submit the exam attempt because the time is up.
     */
    public function submitExamAttemptTimeUp($lessonId, $examId, $submissionId): RedirectResponse
    {
        $submission = Submission::with('answers')->find($submissionId);
        $questions = Question::with(['options' => function ($query) {
            $query->select('id', 'question_id')->where('is_correct', true);
        }])
            ->where('exam_id', $examId)
            ->select('id')
            ->get();

        $score = 0;
        foreach ($questions as $question) {
            $answer = $submission->answers->where('question_id', $question->id)->first();
            if ($answer && $answer->selected_option_id == $question->options->first()->id) {
                $score++;
            }
        }

        $exam = Exam::find($examId);
        $score = ($score / count($questions)) * $exam->total_score;
        $submission->score = $score;
        $submission->is_submitted = true;
        $submission->save();

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
        $exam = Exam::where('id', $examId)->select('title', 'passing_grade', 'hide_score', 'hide_correct_answers')->first();
        if (!$exam) {
            return response()->json(['message' => 'Exam not found'], 404);
        }

        $submission = Submission::with(['answers' => function ($query) {
            $query->select('question_id', 'selected_option_id', 'submission_id', 'id');
        }])
            ->where('id', $submissionId)
            ->where('is_submitted', true)
            ->select('id', 'score', 'updated_at')
            ->first();

        // Hide score from the user
        if ($exam->hide_score) {
            $submission->score = null;
        }

        $questions = Question::with(['options' => function ($query) {
            $query->select('id', 'question_id', 'option_text', 'is_correct');
        }])
            ->where('exam_id', $examId)
            ->select('id', 'question_text')
            ->get();

        // Hide correct answers from the user
        if ($exam->hide_correct_answers) {
            foreach ($questions as $question) {
                foreach ($question->options as $option) {
                    $option->is_correct = null;
                }
            }
        }

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

    public function saveAnswer(Request $request)
    {
        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'question_id' => 'required|exists:questions,id',
            'selected_option_id' => 'required|exists:options,id',
        ]);

        $this->checkIfTimeIsUp(Submission::find($request->submission_id));

        $answer = Answer::where('submission_id', $request->submission_id)
            ->where('question_id', $request->question_id)
            ->first();

        if ($answer) {
            $answer->selected_option_id = $request->selected_option_id;
            $answer->save();
        } else {
            $answer = Answer::create([
                'submission_id' => $request->submission_id,
                'question_id' => $request->question_id,
                'selected_option_id' => $request->selected_option_id,
            ]);
        }

        return response()->json([
            'message' => 'Answer saved',
            'data' => [
                'selected_option_id' => $answer->selected_option_id,
                'question_id' => $answer->question_id
            ],
        ]);
    }

    public function checkIfTimeIsUp($submission)
    {
        $examDuration = Exam::where('id', $submission->exam_id)->select('duration')->first()->duration;

        if ($submission->created_at->diffInSeconds(now()) > $examDuration) {
            $this->submitExamAttemptTimeUp($submission->exam->lesson_id, $submission->exam_id, $submission->id);
        }
    }
}
