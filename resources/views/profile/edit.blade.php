<x-app-layout>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    @if (session('status'))
    <div class="p-4 bg-green-100 text-green-700 dark:bg-green-700 dark:text-green-100">
        {{ session('status') }}
        @echo Storage::files('public/profile-pictures')
    </div>
    @endif


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Upload Profile Picture') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Ensure your profile picture is up to date.') }}
                    </p>

                    <form method="post" action="{{ route('profile.updatePicture') }}" class="mt-6 space-y-6" enctype="multipart/form-data" id="uploadPicture">
                        @csrf
                        @method('patch')

                        <div>
                            <x-input-label for="profile_picture" :value="__('Photo')" />
                            <x-input-picture id="pictureInput" name="profile_picture" class="mt-1 block w-full" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('profile_picture')" />
                        </div>

                        <div class="flex items center gap-4">
                            <x-primary-button id="uploadButton">{{ __('Save') }}</x-primary-button>

                            @if (session('status') === 'profile-photo-updated')
                            <p
                                x-data="{ show: true }"
                                x-show="show"
                                x-transition
                                x-init="setTimeout(() => show = false, 2000)"
                                class="text-sm text-gray-600 dark:text-gray-400">{{ __('Saved.') }}</p>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#uploadButton').click(function() {
                const fileInput = $('#pictureInput')[0]; // Get the file input element
                const file = fileInput.files[0]; // Get the selected file

                if (file) {
                    const reader = new FileReader(); // Create a FileReader object

                    reader.onload = function(event) {
                        // Get the Base64 string
                        const base64String = event.target.result;

                        // Save the Base64 string to local storage
                        localStorage.setItem('profilePicture', base64String);
                        localStorage.setItem('lastUpdatedProfilePicture', new Date().getTime());
                    };

                    // Read the file as a data URL (Base64)
                    reader.readAsDataURL(file);
                } else {
                    alert('Please select an image file first.');
                }
            });

            // Optional: Load the image from local storage when the page loads
            // const savedImage = localStorage.getItem('uploadedImage');
            // if (savedImage) {
            //     $('#uploadedImage').attr('src', savedImage).show(); // Set src and show the image
            // }
        });
    </script>
</x-app-layout>