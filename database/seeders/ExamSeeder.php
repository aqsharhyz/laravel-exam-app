<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Enroll;
use Illuminate\Database\Seeder;
use App\Models\Lesson;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Option;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'a',
            'email' => 'a@g.com',
            'email_verified_at' => now(),
            'password' => Hash::make('s'),
            'remember_token' => Str::random(10),
            'role' => 'admin',
        ]);

        $lesson = Lesson::create([
            'title' => 'Test Lesson',
            'description' => 'This is a test lesson',
        ]);

        Enroll::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        $exam = Exam::create([
            'lesson_id' => $lesson->id,
            'title' => 'Test Exam',
            'description' => 'This is a test exam',
            'duration' => 3600,
            'total_score' => 100,
            'passing_grade' => 60,
            'start_time' => now(),
            'end_time' => now()->addMonth(),
        ]);

        $question = Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'What is 1 + 1?',
        ]);

        Option::create([
            'question_id' => $question->id,
            'option_text' => '1',
            'is_correct' => false,
        ]);

        Option::create([
            'question_id' => $question->id,
            'option_text' => '2',
            'is_correct' => true,
        ]);

        Option::create([
            'question_id' => $question->id,
            'option_text' => '3',
            'is_correct' => false,
        ]);

        Option::create([
            'question_id' => $question->id,
            'option_text' => '4',
            'is_correct' => false,
        ]);

        $question = Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'What is 2 + 2?',
        ]);

        Option::create([
            'question_id' => $question->id,
            'option_text' => '1',
            'is_correct' => false,
        ]);

        Option::create([
            'question_id' => $question->id,
            'option_text' => '4',
            'is_correct' => true,
        ]);

        $lesson = Lesson::create([
            'title' => 'Test Lesson 2',
            'description' => 'This is a test lesson 2',
        ]);

        $exam = Exam::create([
            'lesson_id' => $lesson->id,
            'title' => 'Test Exam 2',
            'description' => 'This is a test exam 2',
            'duration' => 3600,
            'total_score' => 100,
            'passing_grade' => 60,
            'start_time' => now(),
            'end_time' => now()->addMonth(),
        ]);

        $question = Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'What is 3 + 3?',
        ]);

        Option::create([
            'question_id' => $question->id,
            'option_text' => '1',
            'is_correct' => false,
        ]);

        Option::create([
            'question_id' => $question->id,
            'option_text' => '2',
            'is_correct' => false,
        ]);

        Option::create([
            'question_id' => $question->id,
            'option_text' => '6',
            'is_correct' => true,
        ]);
    }
}
