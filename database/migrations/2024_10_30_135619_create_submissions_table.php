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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('score')->nullable();
            $table->foreignId('exam_id')->constrained(table: 'exams', column: 'id')->onDelete('cascade');
            // $table->foreignId('user_id')->constrained(table: 'users', column: 'id')->onDelete('cascade');
            $table->foreignId('enroll_id')->constrained(table: 'enrolls', column: 'id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
