<x-app-layout>
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8 bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold mb-6">{{ isset($lesson->id) ? 'Edit Lesson' : 'Create Lesson' }}</h1>
        <form action="{{ isset($lesson->id) ? route('lessons.update', ['lessonId' => $lesson->id]) : route('lessons.store') }}" method="POST">
            @csrf
            @if(isset($lesson->id))
            @method('PATCH')
            @endif

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $lesson->title) }}" class="mt-1 p-2 w-full border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 @error('title') is-invalid @enderror">
                @error('title')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" class="mt-1 p-2 w-full border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 @error('title') is-invalid @enderror">{{ old('description', $lesson->description) }}</textarea>
                @error('description')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="visibility" class="block text-sm font-medium text-gray-700">Visibility</label>
                <select name="visibility" id="visibility" class="mt-1 p-2 w-full border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="public" {{ old('visibility', $lesson->visibility) === 'public' ? 'selected' : '' }}>Public</option>
                    <option value="private" {{ old('visibility', $lesson->visibility) === 'private' ? 'selected' : '' }}>Private</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="is_active" class="inline-flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $lesson->is_active) ? 'checked' : '' }} class="mr-2">
                    <span class="text-sm font-medium text-gray-700">Active</span>
                </label>
            </div>

            <div class="mb-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200 ease-in-out">
                    {{ isset($lesson->id) ? 'Update' : 'Create' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

<style>
    .alert {
        background-color: #f8d7da;
        color: #721c24;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }

    .alert ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .alert li {
        list-style-type: disc;
    }
</style>