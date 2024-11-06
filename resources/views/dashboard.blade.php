<x-app-layout>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
                    <p class="mb-4">Welcome to the dashboard!</p>
                    <p class="mb-4">You are logged in!</p>
                    <div id="report" route={{ route('profile.reportData') }} class="mb-4">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function build_report(data) {
            const report = `
                <h2 class="text-xl font-bold mb-4">Report</h2>
                <ul class="space-y-4">
                    <li class="p-4 rounded-lg border border-gray-600 bg-gray-700 shadow">
                        <p class="text-lg font-semibold text-blue-300 hover:underline">
                            Total Active Lessons: ${data.total_lessons}
                        </p>
                    </li>
                    <li class="p-4 rounded-lg border border-gray-600 bg-gray-700 shadow">
                        <p class="text-lg font-semibold text-blue-300 hover:underline">
                            Total Exams Submission: ${data.total_exams_submission}
                        </p>
                    </li>
                </ul>
            `;
            $('#report').html(report);
        }

        $(document).ready(function() {
            $('#report').html('Loading...');
            try {
                const data = JSON.parse(localStorage.getItem('reportData'));
                build_report(data);
            } catch {
                $.ajax({
                    url: $('#report').attr('route'),
                    success: function(response) {
                        const data = response.data;
                        try {
                            build_report(data);
                            localStorage.setItem('reportData', JSON.stringify(data));
                        } catch (e) {
                            $('#report').html('<p class="text-red-500">Failed to load report data</p>');
                        }

                    },
                    error: function(error) {
                        $('#report').html('<p class="text-red-500">Failed to load report data</p>');
                    }
                });
            }
        });
    </script>
</x-app-layout>