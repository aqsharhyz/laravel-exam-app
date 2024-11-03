<x-app-layout>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <h1>{{ isset($exam->id) ? 'Edit Exam' : 'Create Exam' }}</h1>
        <form id="exam-form" action="{{ isset($exam->id) ? route('exams.update', ['lessonId' => $lesson->id,'examId' => $exam->id]) : route('exams.store') }}" method="POST">
            @csrf
            @if(isset($exam->id))
            @method('PATCH')
            @endif
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $exam->title) }}" class="mt-1 p-2 w-full border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" class="mt-1 p-2 w-full border border-gray-300 rounded">{{ old('description', $exam->description) }}</textarea>
            </div>
            <div class="mb-4">
                <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                <input type="datetime-local" name="start_time" id="start_time" value="{{ old('start_time', $exam->start_time) }}" class="mt-1 p-2 w-full border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                <input type="datetime-local" name="end_time" id="end_time" value="{{ old('end_time', $exam->end_time) }}" class="mt-1 p-2 w-full border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="duration" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                <input type="number" name="duration" id="duration" value="{{ old('duration', $exam->duration) }}" class="mt-1 p-2 w-full border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="passing_grade" class="block text-sm font-medium text-gray-700">Passing Grade (%)</label>
                <input type="number" name="passing_grade" id="passing_grade" value="{{ old('passing_grade', $exam->passing_grade) }}" class="mt-1 p-2 w-full border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="total_score" class="block text-sm font-medium text-gray-700">Total Score</label>
                <input type="number" name="total_score" id="total_score" value="{{ old('total_score', $exam->total_score) }}" class="mt-1 p-2 w-full border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="lesson_id" class="block text-sm font-medium text-gray-700">Lesson</label>
                @if(isset($lesson))
                <input type="text" value="{{ $lesson->title }}" class="mt-1 p-2 w-full border border-gray-300 rounded" disabled>
                <input type="hidden" name="lesson_id" value="{{ $lesson->id }}">
                @else
                <select name="lesson_id" id="lesson_id" class="mt-1 p-2 w-full border border-gray-300 rounded">
                    @foreach($lessons as $lesson)
                    <option value="{{ $lesson->id }}" {{ old('lesson_id', $exam->lesson_id) == $lesson->id ? 'selected' : '' }}>{{ $lesson->title }}</option>
                    @endforeach
                </select>
                @endif
            </div>

            <div id="questions-container">
            </div>
            <div class="mb-4">
                <button type="button" id="add-question" class="btn btn-secondary">Add Another Question</button>
                <br><br>
            </div>
            <div class="mb-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ isset($exam->id) ? 'Update' : 'Create' }}
                </button>
            </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            let questions = JSON.parse(localStorage.getItem("questions")) || <?php echo json_encode($questions); ?>;
            let questionIndex = questions ? questions.length : 0;
            let options = JSON.parse(localStorage.getItem("options")) || <?php echo json_encode($options); ?>;
            let correct_option = JSON.parse(localStorage.getItem("correct_option")) || <?php echo json_encode($correct_option); ?>;
            // console.log(questions);
            // console.log(options);
            // console.log(correct_option);
            // console.log(questionIndex);


            questions.forEach((question, index) => {
                const question_text = question.question_text || '';
                const options_for_question = options[index] || [];
                const correct_option_for_question = correct_option[index] || 0;
                const newQuestionBlock = `
                    <div class="question-block">
                        <label for="question[${index}]">Question:</label>
                        <input type="text" name="questions[${index}]" value="${question_text}" required>
                        <div class="options-container"></div>
                        </div>
                    `;
                $("#questions-container").append(newQuestionBlock);

                for (let i = 0; i < options_for_question.length; i++) {
                    const option_text = options_for_question[i].option_text || '';
                    const newOptionBlock = `
                        <div class="option-block">
                            <label for="options[${index}]">Option:</label>
                            <input type="text" name="options[${index}][${i}]" value="${option_text}" required>
                            <input type="radio" name="correct_option[${index}]" value="${
                        (i + 1)
                    }" ${correct_option_for_question == (i + 1) ? 'checked' : ''}> Correct
                        </div>
                    `;

                    $(`#questions-container .question-block:eq(${index}) .options-container`).append(newOptionBlock);
                }

                const button_to_add_option = `
                    <button type="button" class="add-option btn btn-secondary">Add Another Option</button>
                    <br><br>
                    `;

                $(`#questions-container .question-block:eq(${index})`).append(button_to_add_option);
            });

            // Add new question block
            $("#add-question").click(function() {
                const newQuestionBlock = `
            <div class="question-block">
                <label for="question[${questionIndex}]">Question:</label>
                <input type="text" name="questions[${questionIndex}]" required>
                <div class="options-container">
                    <div class="option-block">
                        <label for="options[${questionIndex}]">Option:</label>
                        <input type="text" name="options[${questionIndex}][0]" required>
                        <input type="radio" name="correct_option[${questionIndex}]" value="1"> Correct
                    </div>
                    <div class="option-block">
                        <label for="options[${questionIndex}]">Option:</label>
                        <input type="text" name="options[${questionIndex}][1]" required>
                        <input type="radio" name="correct_option[${questionIndex}]" value="2"> Correct
                    </div>
                </div>
                <button type="button" class="add-option btn btn-secondary">Add Another Option</button>
            </div>
        `;
                $("#questions-container").append(newQuestionBlock);
                questionIndex++;
                // console.log("questionIndex ", questionIndex);
            });

            // Add new option block
            $(document).on("click", ".add-option", function() {
                const optionIndex = $(this)
                    .siblings(".options-container")
                    .children(".option-block").length;
                const questionBlockIndex = $(this).closest(".question-block").index();
                // console.log("questionBlockIndex ", questionBlockIndex);

                const newOptionBlock = `
            <div class="option-block">
                <label for="options[${questionBlockIndex}]">Option:</label>
                <input type="text" name="options[${questionBlockIndex}][${optionIndex}]" required>
                <input type="radio" name="correct_option[${questionBlockIndex}]" value="${
            optionIndex + 1
        }"> Correct
            </div>
        `;
                $(this).siblings(".options-container").append(newOptionBlock);
            });

            $('#exam-form').submit(function() {
                let questions_local = []
                let options_local = [];
                let correct_option_local = [];
                $(".question-block").each(function(index) {
                    const question_text = $(this).find("input[name^='questions']").val();
                    questions_local.push({
                        question_text: question_text
                    });

                    let options_for_question = [];
                    let correct_option_for_question = 0;
                    $(this).find(".option-block").each(function(i) {
                        const option_text = $(this).find("input[name^='options']").val();
                        options_for_question.push({
                            option_text: option_text
                        });

                        if ($(this).find("input[name^='correct_option']").is(":checked")) {
                            correct_option_for_question = i + 1;
                        }
                    });

                    options_local.push(options_for_question);
                    correct_option_local.push(correct_option_for_question);
                });

                // console.log(questions_local);
                // console.log(options_local);
                // console.log(correct_option_local);

                localStorage.setItem("questions", JSON.stringify(questions_local));
                localStorage.setItem("options", JSON.stringify(options_local));
                localStorage.setItem("correct_option", JSON.stringify(correct_option_local));

                // $("<input />").attr("type", "hidden")
                //     .attr("name", "questions")
                //     .attr("value", JSON.stringify(questions))
                //     .appendTo("#exam-form");

                // $("<input />").attr("type", "hidden")
                //     .attr("name", "options")
                //     .attr("value", JSON.stringify(options))
                //     .appendTo("#exam-form");

                // $("<input />").attr("type", "hidden")
                //     .attr("name", "correct_option")
                //     .attr("value", JSON.stringify(correct_option))
                //     .appendTo("#exam-form");
            });
        });
    </script>
</x-app-layout>