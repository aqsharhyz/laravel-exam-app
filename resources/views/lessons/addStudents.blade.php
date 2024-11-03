<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8 bg-gray-800 text-white rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4">Add Students</h1>

        <form action="{{ route('lessons.storeStudent', ['lessonId' => $lesson->id]) }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label class="font-semibold">Students</label>
                <select name="students[]" class="form-select mt-2 block w-full" multiple>
                    @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add
            </button>
        </form>
    </div>
</x-app-layout>