<x-app-layout>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8 shadow-md rounded-lg bg-gray-800 text-white">
        <div id="submission" route="{{ route('submissions.getData', ['lessonId' => $lessonId, 'examId' => $examId, 'submissionId' => $submissionId]) }}" name="submissionData_{{ $submissionId }}">
        </div>

        <a href="{{ route('exams.index', ['lessonId' => $lessonId]) }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Back to Exams
        </a>
    </div>
    <script>
        function build_submission(data) {
            const status = data.submission.score >= data.exam.passing_grade ? 'Passed' : 'Failed';
            const submission_header = `
                <h1 class="text-2xl font-bold mb-4">Submitted Exam</h1>
                <h2 class="text-xl mb-2">${data.exam.title}</h2>
                ${data.exam.hide_score ? '' : `
                    ${data.exam.passing_grade >= 0 ? `<p class="mb-2 ${status == 'Passed' ? 'text-green-500' : 'text-red-500'}">${status}</p>` : ''}
                    <p class="mb-2">Score: ${data.submission.score || 0}</p>
                    ${data.exam.passing_grade >= 0 ? `<p class="mb-4">Passing Grade: ${data.exam.passing_grade}</p>` : ''}
                `}
                <p class="mb-4">Submitted: ${data.submission.updated_at}</p>
            `; //! total question

            const questions = data.questions.map(question => {
                const userAnswer = data.submission.answers.find(answer => answer.question_id === question.id)?.selected_option_id;
                const isAnswered = userAnswer !== undefined;
                const correctOption = question.options.find(option => option.is_correct);
                const hideCorrectAnswer = data.exam.hide_correct_answers;

                const questionOptions = question.options.map(option => {
                    let optionText = option.option_text;
                    if (hideCorrectAnswer && isAnswered && userAnswer === option.id) {
                        optionText = `<strong>${option.option_text}</strong>`;
                    } else if (hideCorrectAnswer && isAnswered) {
                        optionText = `<span class="text-gray-400">${option.option_text}</span>`;
                    } else if (hideCorrectAnswer) {
                        optionText = `<span class="text-gray-100">${option.option_text}</span>`;
                    } else if (option.is_correct && userAnswer === option.id) {
                        optionText = `<strong>${option.option_text} (Correct)</strong>`;
                    } else if (!option.is_correct && userAnswer === option.id) {
                        optionText = `<strong>${option.option_text} (Incorrect)</strong>`;
                    } else if (!isAnswered && option.is_correct) {
                        optionText = `<span class="text-gray-900">${option.option_text} (Correct Answer)</span>`;
                    }
                    return `<p>${optionText}</p>`;
                }).join('');
                return `
                    ${hideCorrectAnswer ? `
                        <div class="form-group mb-4 p-4 rounded-lg ${isAnswered ? 'bg-blue-500' : 'bg-yellow-600'}">
                    ` : `
                        <div class="form-group mb-4 p-4 rounded-lg ${isAnswered ? (userAnswer === correctOption.id ? 'bg-green-500' : 'bg-red-500') : 'bg-yellow-600'}">
                    `}
                        <label class="font-semibold">${question.question_text}</label>
                        ${questionOptions}
                    </div>
                `;
            });
            const submission = `
                ${submission_header}
                ${questions.join('')}
            `;
            $('#submission').html(submission);
        }

        $(document).ready(function() {
            $('#submission').html('Loading...');
            if (localStorage.getItem($('#submission').attr('name'))) {
                const data = JSON.parse(localStorage.getItem($('#submission').attr('name')));
                build_submission(data);
            } else {
                $.ajax({
                    url: $('#submission').attr('route'),
                    success: function(response) {
                        const data = response.data;
                        try {
                            build_submission(data);
                            // localStorage.setItem('submissionData_' + data.submission.id, JSON.stringify(data));
                        } catch (e) {
                            console.log(e);

                            $('#submission').html('<p class="text-red-500">Failed to load submission data</p>');
                        }
                    },
                    error: function(error) {
                        $('#submission').html('<p class="text-red-500">Failed to load submission data</p>');
                    }
                });
            }
        });
    </script>
</x-app-layout>