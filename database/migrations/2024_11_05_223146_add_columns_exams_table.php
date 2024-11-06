<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->boolean('hide_score')->default(false);
            $table->boolean('hide_correct_answers')->default(false);
            $table->integer('passing_grade')->default(-1)->change();
            $table->boolean('multiple_attempts')->default(false);
            // $table->boolean('randomize_questions')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('hide_score');
            $table->dropColumn('hide_correct_answers');
            $table->integer('passing_grade')->change();
            $table->dropColumn('multiple_attempts');
            // $table->dropColumn('randomize_questions');
        });
    }
};
