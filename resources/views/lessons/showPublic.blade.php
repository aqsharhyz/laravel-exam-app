<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8 shadow-md rounded-lg bg-gray-800">
        <h1 class="text-2xl font-bold mb-4 text-white">Show Public Lesson List</h1>
        <ul class="space-y-4">
            @foreach($lessons as $lesson)
            <li class="p-4 rounded-lg border border-gray-600 bg-gray-700 shadow">
                <a href="{{ route('lessons.show', ['lessonId' => $lesson->id]) }}" class="text-lg font-semibold text-blue-300 hover:underline">
                    {{ $lesson->title }}
                </a>
                <p class="text-gray-300">{{ $lesson->description }}</p>
            </li>
            @endforeach
        </ul>
    </div>
</x-app-layout>