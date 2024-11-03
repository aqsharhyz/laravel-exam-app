<x-app-layout>
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @elseif(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8 bg-gray-700 shadow-md rounded-lg">
        <h1 class="text-2xl font-bold mb-4 text-white">{{ $lesson->title }}
            @if($is_enrolled)
            <span class="text-sm text-green-600 font-medium">Enrolled</span>
            @endif
        </h1>
        <p class="text-gray-200 mb-6">{{ $lesson->description }}</p>

        @if(!$is_enrolled)
        <form method="POST" action="{{ route('lessons.enroll', ['lessonId' => $lesson->id]) }}">
            @csrf
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                Enroll
            </button>
        </form>
        @else
        <div class="flex items-center space-x-4 mb-4">
            <form method="POST" action="{{ route('lessons.unenroll', ['lessonId' => $lesson->id]) }}">
                @csrf
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                    Unenroll
                </button>
            </form>
            @if(Auth::user()->role == 'admin')
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                <a href="{{ route('lessons.edit', ['lessonId' => $lesson->id]) }}" class="text-white hover:underline">Edit</a>
            </button>
            @endif
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                <a href="{{ route('exams.index', ['lessonId' => $lesson->id]) }}" class="text-white hover:underline">Exams</a>
            </button>
        </div>
        @endif
    </div>
</x-app-layout>

<style>
    .text-gray-700 {
        color: #4a5568;
        /* Dark gray for text */
    }

    .text-green-600 {
        color: #48bb78;
        /* Green for "Enrolled" message */
    }

    .shadow-md {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        /* Soft shadow for the card */
    }
</style>