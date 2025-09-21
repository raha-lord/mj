<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('ui.tasks') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Vue App Container -->
            <div id="task-manager-app">
                <!-- Fallback content пока Vue не загрузился -->
                <div class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                    <span class="ml-2 text-gray-600 dark:text-gray-300">Загрузка приложения...</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.taskManagerInitialData = {
            projects: @json($projects ?? []),
            statuses: @json($statuses ?? []),
            sizes: @json($sizes ?? []),
            users: @json($users ?? [])
        };
    </script>

    <style>
        .priority-urgent {
            background-color: rgb(254, 226, 226);
            color: rgb(153, 27, 27);
        }
        .priority-high {
            background-color: rgb(255, 237, 213);
            color: rgb(154, 52, 18);
        }
        .priority-normal {
            background-color: rgb(219, 234, 254);
            color: rgb(30, 64, 175);
        }
        .priority-low {
            background-color: rgb(220, 252, 231);
            color: rgb(22, 101, 52);
        }
    </style>
</x-app-layout>