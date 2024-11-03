<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8 bg-gray-800 text-white rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4">Attempt Exam</h1>

        <div id="timer" data-duration="{{ $duration }}" class="mb-4">
            Time remaining: <span id="time" class="font-semibold">{{ $duration }}</span> seconds
        </div>

        <form action="{{ route('exams.submitAttempt', ['lessonId' => $lessonId, 'examId' => $examId, 'submissionId' => $submissionId]) }}" method="POST" id="examForm">
            @csrf
            @foreach($questions as $question)
            <div class="form-group mb-4">
                <label class="font-semibold">{{ $question->question_text }}</label>
                @foreach($question->options as $option)
                <div class="form-check mt-2">
                    <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" id="option{{ $option->id }}">
                    <label class="form-check-label" for="option{{ $option->id }}">
                        {{ $option->option_text }}
                    </label>
                </div>
                @endforeach
            </div>
            @endforeach
            <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Submit
            </button>
        </form>
    </div>

    <script>
        let timeLeft = parseInt(document.getElementById('timer').dataset.duration);
        const timerElement = document.getElementById('time');

        const countdown = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(countdown);
                document.getElementById('examForm').submit(); // Automatically submit the form when time runs out
            } else {
                timerElement.textContent = timeLeft;
                timeLeft--;
            }
        }, 1000);
    </script>
</x-app-layout>