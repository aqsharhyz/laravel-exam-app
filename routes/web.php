<?php

use App\Http\Controllers\ExamController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
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
});

Route::middleware(['auth', AdminMiddleware::class])->prefix('lessons')->group(function () {
    Route::get('/create', [LessonController::class, 'create'])->name('lessons.create');
    Route::post('/store', [LessonController::class, 'store'])->name('lessons.store');
    Route::get('/{lessonId}/edit', [LessonController::class, 'edit'])->name('lessons.edit');
    Route::get('/{lessonId}/add', [LessonController::class, 'addStudents'])->name('lessons.addStudents');
    Route::post('/{lessonId}/add', [LessonController::class, 'storeStudent'])->name('lessons.storeStudent');
    Route::patch('/{lessonId}', [LessonController::class, 'update'])->name('lessons.update');
    Route::delete('/{lessonId}', [LessonController::class, 'destroy'])->name('lessons.destroy');
    Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
    Route::get('/{lessonId}/exams/create', [ExamController::class, 'create'])->name('exams.createInLesson');
    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
    Route::post('/{lessonId}/exams', [ExamController::class, 'store'])->name('exams.storeInLesson');
    Route::get('/{lessonId}/exams/{examId}/edit', [ExamController::class, 'edit'])->name('exams.edit');
    Route::patch('/{lessonId}/exams/{examId}', [ExamController::class, 'update'])->name('exams.update');
    Route::delete('/{lessonId}/exams/{examId}', [ExamController::class, 'destroy'])->name('exams.destroy');
});

Route::middleware('auth')->prefix('lessons/{lessonId}')->group(function () {
    Route::get('/', [LessonController::class, 'show'])->name('lessons.show');
    Route::post('/enroll', [LessonController::class, 'enroll'])->name('lessons.enroll');
    Route::post('/unenroll', [LessonController::class, 'unenroll'])->name('lessons.unenroll');

    Route::get('/exams/', [ExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/{examId}', [ExamController::class, 'show'])->name('exams.show');
    Route::get('/exams/{examId}/attempt', [ExamController::class, 'attempt'])->name('exams.showAttemptForm');
    // Route::get('/exams/{examId}/attempt/{submissionId}', [ExamController::class, 'attempted'])->name('exams.showAttemptedForm');
    Route::post('/exams/{examId}/attempt/{submissionId}', [ExamController::class, 'submit'])->name('exams.submitAttempt');
    Route::get('/exams/{examId}/submissions/{submissionId}', [ExamController::class, 'showSubmission'])->name('exams.showSubmission');
    Route::get('/exams/{examId}/submissions/{submissionId}/data', [ExamController::class, 'getSubmissionData'])->name('exams.getSubmissionData');
});

require __DIR__ . '/auth.php';
