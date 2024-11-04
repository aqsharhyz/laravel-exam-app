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
        Gate::authorize('viewAny', Lesson::class);

        return view('lessons.showPublic', [
            'lessons' => Lesson::all(),
        ]);
    }

    public function showActive()
    {
        Gate::authorize('viewAny', Lesson::class);

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
        Gate::authorize('create', Lesson::class);
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
        Gate::authorize('view', Lesson::findOrFail($lessonId));

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
        Gate::authorize('update', Lesson::findOrFail($lessonId));

        return view('lessons.edit', [
            'lesson' => Lesson::findOrFail($lessonId),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LessonRequest $request, $lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);

        Gate::authorize('update', $lesson);

        $lesson->update($request->validated());

        return redirect()->route('lessons.show', ['lessonId' => $lesson->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($lessonId)
    {
        //! soft delete
        $lesson = Lesson::findOrFail($lessonId);
        Gate::authorize('delete', $lesson);
        $lesson->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Lesson deleted successfully');
    }
}
