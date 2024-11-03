<?php

namespace App\Http\Controllers;

use App\Http\Requests\LessonRequest;
use App\Models\Lesson;
use App\Models\Enroll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showPublic()
    {
        return view('lessons.showPublic', [
            'lessons' => Lesson::all(),
        ]);
    }

    public function showActive()
    {
        return view('lessons.showActive', [
            'lessons' => Lesson::whereHas('enrolls', function ($query) {
                $query->where('user_id', Auth::id());
            })->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('lessons.edit', [
            'lesson' => new Lesson(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LessonRequest $request)
    {
        Gate::authorize('create', Lesson::class);
        $lesson = Lesson::create($request->validated());

        return redirect()->route('lessons.show', ['lessonId' => $lesson->id])->with('success', 'Lesson created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($lessonId)
    {
        return view('lessons.show', [
            'lesson' => Lesson::findOrFail($lessonId),
            'is_enrolled' => Enroll::where('user_id', Auth::id())->where('lesson_id', $lessonId)->exists(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($lessonId)
    {
        return view('lessons.edit', [
            'lesson' => Lesson::findOrFail($lessonId),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LessonRequest $request, $lessonId)
    {
        Gate::authorize('update', Lesson::findOrFail($lessonId));

        $lesson = Lesson::findOrFail($lessonId);
        $lesson->update($request->validated());

        return redirect()->route('lessons.show', ['lessonId' => $lesson->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($lessonId)
    {
        //! soft delete
        Lesson::findOrFail($lessonId)->delete();
        return redirect()->route('admin.dashboard')->with('success', 'Lesson deleted successfully');
    }

    public function enroll($lessonId)
    {
        if (Enroll::where('user_id', Auth::id())->where('lesson_id', $lessonId)->exists()) {
            return redirect()->route('lessons.showActive');
            //!
        }

        if (!Lesson::findOrFail($lessonId)->is_active) {
            // return redirect()->route('lessons.showActive');
            //!
        }

        if (!(Lesson::findOrFail($lessonId)->visibility === 'public')) {
            // return redirect()->route('lessons.showActive');
            //!
        }

        Enroll::create([
            'user_id' => Auth::id(),
            'lesson_id' => $lessonId,
        ]);

        return redirect()->back()->with('success', 'Enrolled successfully');
    }

    public function unenroll($lessonId)
    {
        if (!Enroll::where('user_id', Auth::id())->where('lesson_id', $lessonId)->exists()) {
            return redirect()->back()->with('error', 'You are not enrolled in this lesson');
        }
        Enroll::where('user_id', Auth::id())->where('lesson_id', $lessonId)->delete();
        return redirect()->back()->with('success', 'Unenrolled successfully');
    }

    public function addStudents($lessonId)
    {
        return view('lessons.addStudents', [
            'lesson' => Lesson::findOrFail($lessonId),
            'students' => User::all(),
        ]);
    }

    public function storeStudent(Request $request, $lessonId)
    {
        $request->validate([
            'emails' => 'required|string|email',
        ]);

        $emails = explode(',', $request->emails);
        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                Enroll::create([
                    'user_id' => $user->id,
                    'lesson_id' => $lessonId,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Students added successfully');
    }
}
