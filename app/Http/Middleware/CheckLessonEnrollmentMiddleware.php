<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Enroll;

class CheckLessonEnrollmentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lessonId = $request->route('lessonId');

        $userId = Auth::id();

        // Check if there's an enrollment record for this user and lesson
        $enrolled = Enroll::where('user_id', $userId)
            ->where('lesson_id', $lessonId)->first();

        if (!$enrolled) {
            // Redirect if not enrolled
            // return redirect()->route('lessons.index')->with('error', 'You are not enrolled in this lesson.');
            //!
            return redirect()->route('lessons.show', ['lessonId' => $lessonId]);
        }

        $request->attributes->set('enrolled_id', $enrolled->id);

        return $next($request);
    }
}
