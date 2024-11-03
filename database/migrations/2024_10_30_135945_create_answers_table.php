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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('question_id')->constrained(table: 'questions', column: 'id')->onDelete('cascade');
            $table->foreignId('submission_id')->constrained(table: 'submissions', column: 'id')->onDelete('cascade');
            $table->foreignId('selected_option_id')->constrained(table: 'options', column: 'id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
