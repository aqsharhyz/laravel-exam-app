<?php

namespace App\Http\Controllers;

use App\Models\Enroll;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     */
    public function show(Enroll $enroll)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enroll $enroll)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Enroll $enroll)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
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
