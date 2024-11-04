<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration' => 'required|integer|min:1',
            'total_score' => 'required|integer|min:1',
            'passing_grade' => 'required|integer|min:1|max:' . $this->input('total_score'),
            'lesson_id' => 'required|exists:lessons,id',
            'questions.*' => 'required|string|max:255',
            'options.*.*' => 'required|string|max:255',
            'correct_option.*' => 'required|integer|min:1', // Ensure one correct option is selected
        ];
    }
}
