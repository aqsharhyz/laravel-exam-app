<x-app-layout>
    <!-- Button Container -->
    <div class="max-w-2xl mx-auto mt-4 flex space-x-4">
        <a href="{{ route('lessons.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create New Lesson</a>
        <a href="{{ route('exams.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create New Exam</a>
    </div>

    <!-- Main Content -->
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8 mt-4">
        @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('success') }}
        </div>
        @endif

        @foreach($lessons as $lesson)
        <div class="bg-gray-700 shadow sm:rounded-lg mb-4 p-4">
            <a href="{{ route('lessons.show', ['lessonId' => $lesson->id]) }}" class="text-xl font-semibold text-blue-300 hover:underline">{{ $lesson->title }}</a>
            <p class="text-sm text-gray-300">{{ $lesson->description }}</p>
            <div class="mt-4 flex space-x-4">
                <a href="{{ route('lessons.edit', ['lessonId' => $lesson->id]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</a>
                <a href="{{ route('exams.index', ['lessonId' => $lesson->id]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Go to Exam</a>
                {{-- <a href="{{ route('lessons.addStudents', ['lessonId' => $lesson->id]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Add Student</a> --}}

                <form action="{{ route('lessons.destroy', ['lessonId' => $lesson->id]) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</x-app-layout>