<?php

use App\Http\Controllers\ExamController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EnrollController;
use App\Http\Controllers\SubmissionController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [ProfileController::class, 'showReport'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/report', [ProfileController::class, 'reportData'])->name('profile.reportData');
});

Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

Route::middleware('auth')->prefix('lessons')->group(function () {
    Route::get('/', [LessonController::class, 'showPublic'])->name('lessons.showPublic');
    Route::get('/active', [LessonController::class, 'showActive'])->name('lessons.showActive');
    Route::get('/create', [LessonController::class, 'create'])->name('lessons.create');
    Route::post('/store', [LessonController::class, 'store'])->name('lessons.store');

    Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');

    Route::prefix('/{lessonId}')->group(function () {
        Route::get('/', [LessonController::class, 'show'])->name('lessons.show');
        Route::get('/edit', [LessonController::class, 'edit'])->name('lessons.edit');
        Route::patch('/', [LessonController::class, 'update'])->name('lessons.update');
        Route::delete('/', [LessonController::class, 'destroy'])->name('lessons.destroy');

        Route::post('/enroll', [EnrollController::class, 'enroll'])->name('lessons.enroll');
        Route::post('/unenroll', [EnrollController::class, 'unenroll'])->name('lessons.unenroll');
        // Route::get('/add', [LessonController::class, 'addStudents'])->name('lessons.addStudents');
        // Route::post('/add', [LessonController::class, 'storeStudent'])->name('lessons.storeStudent');

        Route::get('/exams/', [ExamController::class, 'index'])->name('exams.index');
        Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.createInLesson');
        Route::post('/exams', [ExamController::class, 'store'])->name('exams.storeInLesson');
        Route::get('/exams/{examId}', [ExamController::class, 'show'])->name('exams.show');
        Route::get('/exams/{examId}/edit', [ExamController::class, 'edit'])->name('exams.edit');
        Route::patch('/exams/{examId}', [ExamController::class, 'update'])->name('exams.update');
        Route::delete('/exams/{examId}', [ExamController::class, 'destroy'])->name('exams.destroy');

        Route::get('/exams/{examId}/attempt', [SubmissionController::class, 'checkAndStartExamAttempt'])->name('submissions.checkAndStartAttempt');
        // Route::get('/exams/{examId}/attempt/{submissionId}', [SubmissionController::class, 'checkAndStartExamAttempt'])->name('submissions.checkAndStartAttempt');
        Route::post('/exams/{examId}/attempt/{submissionId}', [SubmissionController::class, 'saveAnswer'])->name('submissions.saveAnswer');
        Route::post('/exams/{examId}/attempt/{submissionId}/submit', [SubmissionController::class, 'submitExamAttempt'])->name('submissions.submitExamAttempt');
        Route::get('/exams/{examId}/submissions/{submissionId}', [SubmissionController::class, 'show'])->name('submissions.show');
        Route::get('/exams/{examId}/submissions/{submissionId}/data', [SubmissionController::class, 'getData'])->name('submissions.getData');
    });
});

require __DIR__ . '/auth.php';
