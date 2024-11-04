<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8 shadow-md rounded-lg bg-gray-800">
        @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <h1 class="text-2xl font-bold mb-4 text-white">{{ $exam->title }}</h1>
        <p class="mb-2 text-gray-300">Duration: {{ $exam->duration / 60 }} minutes</p>
        <p class="mb-2 text-gray-300">Total Score: {{ $exam->total_score }}</p>
        <p class="mb-2 text-gray-300">Passing Grade: {{ $exam->passing_grade }}</p>
        <p class="mb-2 text-gray-300">{{ $exam->questions_count }} questions</p>
        <p class="mb-4 text-gray-300">{{ $exam->description }}</p>

        <div class="flex items-center space-x-4 mb-4">
            @if(!$is_submitted)
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <a href="{{ route('submissions.checkAndStartAttempt', ['lessonId' => $lessonId, 'examId' => $exam->id]) }}">
                    Attempt
                </a>
            </button>
            @else
            <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                <a href="{{ route('submissions.show', ['lessonId' => $lessonId, 'examId' => $exam->id, 'submissionId' => $submissionId]) }}">
                    View Submission
                </a>
            </button>
            @endif
            @if(Auth::user()->isAdministrator())
            <button class="bg-yellow-500 hover:bg-yellow-700 text-gray-700 hover:text-white font-bold py-2 px-4 rounded">
                <a href="{{ route('exams.edit', ['lessonId' => $lessonId, 'examId' => $exam->id]) }}">
                    Edit
                </a>
            </button>
            @endif
        </div>
    </div>

    @if($clearLocal)
    <script>
        localStorage.removeItem('questions');
        localStorage.removeItem('options');
        localStorage.removeItem('correct_option');
    </script>
    @endif
</x-app-layout>