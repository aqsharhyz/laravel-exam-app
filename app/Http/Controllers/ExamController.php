<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckLessonEnrollmentMiddleware;
use App\Models\Enroll;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Submission;
use App\Models\Answer;
use App\Models\Lesson;
use App\Models\Option;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;

class ExamController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(CheckLessonEnrollmentMiddleware::class, except: ['create', 'store', 'edit', 'update', 'destroy'])
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index($lessonId): View
    {
        // $this->checkIfEnrolled($lessonId);

        $exams = Exam::where('lesson_id', $lessonId)->get();
        return view('exams.index', [
            'exams' => $exams,
            'lessonId' => $lessonId
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($lessonId = null): View
    {
        if ($lessonId) {
            return view('exams.edit', [
                'exam' => new Exam(),
                'lesson' => Lesson::findOrFail($lessonId),
                'questions' => [new Question()],
                'options' => [[new Option(), new Option()]],
                'correct_option' => [-1],
            ]);
            // return view('exams.edit', [
            //     'exam' => new Exam(),
            //     'lesson' => Lesson::findOrFail($lessonId),
            //     'questions' => [['question_text' => 'das'], ['question_text' => 'daa']],
            //     'options' => [
            //         [
            //             ['option_text' => 'dasd'],
            //             ['option_text' => 'dasds'],
            //             ['option_text' => 'dasds'],
            //         ],
            //         [
            //             ['option_text' => 'dasd'],
            //             ['option_text' => 'dasa'],
            //             ['option_text' => 'dasds'],
            //             ['option_text' => 'dasdsaa'],
            //         ],
            //     ],
            //     'correct_option' => [-1, 2],
            // ]);
        }

        return view('exams.edit', [
            'exam' => new Exam(),
            'lessons' => Lesson::all(),
            'questions' => [new Question()],
            'options' => [[new Option(), new Option()]],
            'correct_option' => [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $lessonId = null)
    {
        // $request->attributes->set('lesson_id', $lessonId ?? $request->get('lesson_id'));

        // echo json_encode($request->all());

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration' => 'required|integer|min:1',
            'passing_grade' => 'required|integer|min:1|max:100',
            'total_score' => 'required|integer|min:1',
            'lesson_id' => 'required|exists:lessons,id',
            'questions.*' => 'required|string|max:255',
            'options.*.*' => 'required|string|max:255',
            'correct_option.*' => 'required|integer|min:1', // Ensure one correct option is selected
        ]);

        // echo json_encode($validatedData);

        $exam = Exam::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration' => $request->duration,
            'passing_grade' => $request->passing_grade,
            'total_score' => $request->total_score,
            'lesson_id' => $request->lesson_id,
        ]);

        // echo $exam;

        foreach ($validatedData['questions'] as $index => $question) {
            $newQuestion = Question::create(['question_text' => $question, 'exam_id' => $exam->id]);

            // echo $newQuestion;

            foreach ($validatedData['options'][$index] as $optionIndex => $option) {
                // echo $optionIndex . ' ' . $option;
                // echo $validatedData['correct_option'][$index];
                // echo (($optionIndex + 1) === ((int) $validatedData['correct_option'][$index]));
                Option::create([
                    'question_id' => $newQuestion->id,
                    'option_text' => $option,
                    'is_correct' => ($optionIndex + 1) === (int) $validatedData['correct_option'][$index], // Check if this option is the correct one
                ]);

                // echo $newOption;
            }
        }

        $question = Question::with(['options' => function ($query) {
            $query->select('id', 'question_id', 'option_text');
        }])
            ->where('exam_id', $exam->id)
            ->select('id', 'question_text')
            ->get();

        Cache::put('exam_' . $exam->id, ['exam' => $exam, 'questions' => $question]);


        return redirect()->route('exams.show', [
            'lessonId' => $request->lesson_id,
            'examId' => $exam->id,
        ])->with(['clearLocal' => true]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $lessonId, $examId): View
    {
        $exam = Exam::withCount('questions')->findOrFail($examId);
        $submitted = Submission::where('enroll_id', $request->get('enrolled_id'))->where('exam_id', $examId)->get();

        return view('exams.show', [
            'exam' => $exam,
            'lessonId' => $lessonId,
            'is_submitted' => $submitted->isNotEmpty(),
            'submissionId' => $submitted->last()->id ?? null,
            'clearLocal' => $request->session()->pull('clearLocal', false),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($lessonId, $examId): View
    {
        $exam = Exam::findOrFail($examId);
        $questions = Question::where('exam_id', $examId)->get();
        $options = Option::whereIn('question_id', $questions->pluck('id'))->select('id', 'question_id', 'option_text', 'is_correct')->get();

        // print_r($options);
        // echo $questions;
        // echo $options->groupBy('question_id');
        $s = array_values($options->groupBy('question_id')->toArray());
        // foreach ($s as $key => $value) {
        //     // $d[$key] = $value;
        //     $s[$key - 1] = $s[$key];
        //     unset($s[$key]);
        // }
        $r = array_values($options->where('is_correct', true)->pluck('option_text', 'question_id')->toArray());
        foreach ($s as $key => $value) {
            foreach ($value as $key1 => $v) {
                if ($v['is_correct']) {
                    $r[$key] = $key1 + 1;
                }
            }
        }
        // print_r($r);

        return view('exams.edit', [
            'exam' => $exam,
            'lesson' => Lesson::findOrFail($lessonId),
            'questions' => $questions,
            'options' => $s,
            'correct_option' => $r,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $lessonId, $examId)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration' => 'required|integer|min:1',
            'passing_grade' => 'required|integer|min:1|max:total_score',
            'total_score' => 'required|integer|min:1',
            'lesson_id' => 'required|exists:lessons,id',
            'questions.*' => 'required|string|max:255',
            'options.*.*' => 'required|string|max:255',
            'correct_option.*' => 'required|integer|min:1', // Ensure one correct option is selected
        ]);

        // echo json_encode($validatedData);
        // return;

        $exam = Exam::findOrFail($examId);
        $exam->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration' => $request->duration,
            'passing_grade' => $request->passing_grade,
            'total_score' => $request->total_score,
            'lesson_id' => $request->lesson_id,
        ]);

        $questions = Question::where('exam_id', $examId)->get();
        $options = Option::whereIn('question_id', $questions->pluck('id'))->get();

        // echo json_encode($questions);
        // echo json_encode($options);
        // return;

        foreach ($questions as $question) {
            $question->delete();
        }

        foreach ($options as $option) {
            $option->delete();
        }

        foreach ($validatedData['questions'] as $index => $question) {
            $newQuestion = Question::create(['question_text' => $question, 'exam_id' => $exam->id]);

            foreach ($validatedData['options'][$index] as $optionIndex => $option) {
                Option::create([
                    'question_id' => $newQuestion->id,
                    'option_text' => $option,
                    'is_correct' => ($optionIndex + 1) === (int) $validatedData['correct_option'][$index], // Check if this option is the correct one
                ]);
            }
        }

        $question = Question::with(['options' => function ($query) {
            $query->select('id', 'question_id', 'option_text');
        }])
            ->where('exam_id', $examId)
            ->select('id', 'question_text')
            ->get();


        Cache::put('exam_' . $examId, ['exam' => $exam, 'questions' => $question]);

        return redirect()->route('exams.show', [
            'lessonId' => $request->lesson_id,
            'examId' => $exam->id,
        ])->with(['clearLocal' => true]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        //
    }

    public function attempt(Request $request, $lessonId, $examId)
    {
        $submitted = Submission::where('enroll_id', $request->get('enrolled_id'))->where('exam_id', $examId)->get();
        // echo $submitted;
        // echo $request->get('enrolled_id');

        if ($submitted->isNotEmpty()) {
            return redirect()->route('exams.showSubmission', [
                'lessonId' => $lessonId,
                'examId' => $examId,
                'submissionId' => $submitted->last()->id
            ]);
        }

        return $this->attemptExam($lessonId, $request->get('enrolled_id'), $examId);
    }

    function attemptExam($lessonId, $enrolledId, $examId): View
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

    public function submit(Request $request, $lessonId, $examId, $submissionId)
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
        return redirect()->route('exams.showSubmission', [
            'lessonId' => $lessonId,
            'examId' => $examId,
            'submissionId' => $submissionId
        ]);
    }

    public function showSubmission($lessonId, $examId, $submissionId): View
    {
        return view('exams.showSubmission', [
            'lessonId' => $lessonId,
            'examId' => $examId,
            'submissionId' => $submissionId,
        ]);
    }

    public function getSubmissionData($lessonId, $examId, $submissionId): JsonResponse
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

    function checkIfEnrolled($lessonId)
    {
        return Enroll::where('user_id', Auth::id())->where('lesson_id', $lessonId)->exists();
    }
}
