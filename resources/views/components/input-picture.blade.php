<div class="mb-4">
    <label for="{{ $id ?? 'photo' }}" class="block text-sm font-semibold text-gray-900 dark:text-gray-100">
        {{ $label ?? 'Upload Picture' }}
    </label>
    <input
        type="file"
        class="mt-2 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:ring focus:ring-indigo-500 focus:border-indigo-500 dark:text-gray-400 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
        id="{{ $id ?? 'photo' }}"
        name="{{ $name ?? 'photo' }}"
        accept="image/*">
    @if ($errors->has($name ?? 'photo'))
    <span class="text-sm text-red-600 mt-1">{{ $errors->first($name ?? 'photo') }}</span>
    @endif
</div>