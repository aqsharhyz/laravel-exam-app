<?php

namespace Database\Seeders;

use App\Models\Enroll;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Option;
use App\Models\Question;
use App\Models\Submission;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            ExamSeeder::class,
        ]);

        // User::factory(10)->create();
        for ($i = 0; $i < 2; $i++) {
            $lesson = Lesson::factory()->create([
                'visibility' => 'public',
            ]);

            $enrolls = Enroll::factory(10)->create([
                'lesson_id' => $lesson->id,
            ]);

            for ($j = 0; $j < 2; $j++) {
                $exam = Exam::factory()->create([
                    'lesson_id' => $lesson->id,
                ]);

                foreach ($enrolls as $enroll) {
                    Submission::factory()->create([
                        'exam_id' => $exam->id,
                        'is_submitted' => true,
                        'enroll_id' => $enroll->id,
                    ]);
                }

                for ($k = 0; $k < 2; $k++) {
                    $question = Question::factory()->create([
                        'exam_id' => $exam->id,
                    ]);
                    Option::factory(2)->create([
                        'question_id' => $question->id,
                        'is_correct' => false,
                    ]);
                    Option::factory()->create([
                        'question_id' => $question->id,
                        'is_correct' => true,
                    ]);
                }
            }
        }
    }
}
