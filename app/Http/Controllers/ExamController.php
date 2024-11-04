<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckLessonEnrollmentMiddleware;
use App\Http\Requests\ExamRequest;
use App\Models\Enroll;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Submission;
use App\Models\Lesson;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class ExamController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(CheckLessonEnrollmentMiddleware::class, except: ['create', 'store', 'edit', 'update', 'destroy'])
            //!
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $lessonId): View
    {
        // $this->checkIfEnrolled($lessonId);

        // dd(Enroll::findOrFail('enroll_id', $request->get('enrolled_id')));
        Gate::authorize('viewAny', Exam::where('lesson_id', $lessonId)->first());
        $exams = Exam::where('lesson_id', $lessonId)->get();
        return view('exams.index', [
            'lessonId' => $lessonId,
            'exams' => $exams,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($lessonId = null): View
    {
        Gate::authorize('create', Exam::class);
        if ($lessonId) {
            return view('exams.edit', [
                'exam' => new Exam(),
                'lesson' => Lesson::findOrFail($lessonId),
                'questions' => [new Question()],
                'options' => [[new Option(), new Option()]],
                'correct_option' => [-1],
            ]);
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
    public function store(ExamRequest $request, $lessonId = null)
    {
        Gate::authorize('create', Exam::class);
        // $request->attributes->set('lesson_id', $lessonId ?? $request->get('lesson_id'));

        // echo json_encode($request->all());

        $validatedData = $request->validated();

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
        Gate::authorize('view', $exam);
        $submitted = Submission::where('enroll_id', $request->input('enrolled_id'))->where('exam_id', $examId)->get();

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
        Gate::authorize('update', $exam);
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
    public function update(ExamRequest $request, $lessonId, $examId)
    {
        $exam = Exam::findOrFail($examId);
        Gate::authorize('update', $exam);
        $validatedData = $request->validated();

        //!
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
    public function destroy($examId)
    {
        $exam = Exam::findOrFail($examId);
        Gate::authorize('delete', $exam);
        $exam->delete();
        return redirect()->route('exams.index', ['lessonId' => $exam->lesson_id]);
    }

    // function checkIfEnrolled($lessonId)
    // {
    //     return Enroll::where('user_id', Auth::id())->where('lesson_id', $lessonId)->exists();
    // }
}
