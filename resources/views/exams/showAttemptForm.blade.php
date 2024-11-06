<x-app-layout>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Ensure the modal is hidden by default */
        #warningModal {
            display: none;
            /* Hidden by default */
        }
    </style>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8 bg-gray-800 text-white rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4">Attempt Exam</h1>

        <div id="timer" class="mb-4">
            Time remaining: <span id="time" class="font-semibold"></span> seconds
        </div>

        <form action="{{ route('submissions.submitExamAttempt', ['lessonId' => $lessonId, 'examId' => $examId, 'submissionId' => $submissionId]) }}" method="POST" id="examForm">
            @csrf
            @foreach($questions as $question)
            <div class="form-group mb-4">
                <label class="font-semibold">{{ $question->question_text }}</label>
                @foreach($question->options as $option)
                <div class="form-check mt-2">
                    <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" id="option{{ $option->id }}" {{ $question->selected_option_id == $option->id ? 'checked' : '' }}>
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

        <div id="warningModal" class="modal fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
            <div class="bg-black rounded-lg shadow-lg p-6 w-80">
                <h2 class="text-xl font-semibold mb-4" id="modalTitle">Warning!</h2>
                <div class="mb-4" id="modalMessage"></div>
                <button id="closeModal" class="bg-red-500 text-white py-2 px-4 rounded">Close</button>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let dueTime = '{{ $due }}';
            let timeLeft = Math.floor((new Date(dueTime) - new Date()) / 1000);
            console.log(`Due time: ${dueTime}, Time left: ${timeLeft}`);

            const countdown = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    $('#examForm').submit();
                } else {
                    $('#time').text(timeLeft);
                    timeLeft--;
                }
            }, 1000);

            $('[id^="option"]').on('change', function(event) {
                event.preventDefault();
                const fieldName = $(this).attr('name');
                const fieldValue = $(this).val();

                $.ajax({
                    url: $('#examForm').attr('action').replace('/submit', ''),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        submission_id: '{{ $submissionId }}',
                        question_id: fieldName.split('[')[1].split(']')[0],
                        selected_option_id: fieldValue
                    },
                    success: function(response) {
                        data = response.data;
                        const selected_option_id = data.selected_option_id;
                        if (selected_option_id != fieldValue) {
                            $('#modalTitle').text('Error!');
                            $('#modalMessage').text('Failed to update the selected option. Please try again.');
                            $('#warningModal').css('display', 'flex');
                            return;
                        }
                        const selectedOption = $(`input[name="${fieldName}"][value="${selected_option_id}"]`);
                        selectedOption.prop('checked', true);
                    },
                    error: function(error) {
                        $('#modalTitle').text('Error!');
                        $('#modalMessage').text('Failed to update the selected option. Please check your network connection and try again.');
                        $('#warningModal').css('display', 'flex'); // Show modal
                        console.error('Error:', error);
                    }
                });
            });

            // Close the modal when the user clicks the close button
            $('#closeModal').on('click', function() {
                $('#warningModal').css('display', 'none'); // Hide modal
            });

            // Close the modal when clicking outside of the modal content
            $(window).on('click', function(event) {
                if ($(event.target).is('#warningModal')) {
                    $('#warningModal').css('display', 'none'); // Hide modal
                }
            });
        });
    </script>
</x-app-layout>