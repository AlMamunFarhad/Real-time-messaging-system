<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Admin Dashboard</h4>
            </div>
            <div class="flex items-center gap-4">
                @auth('admin')
                    <x-message-icon />
                @endauth
                <a href="{{ route('admin.logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="text-white dark:text-gray-50 flex items-end space-x-1 btn bg-gray-400 hover:bg-neutral-500 transition p-2 rounded-lg">
                    {{ __('Logout') }}
                </a>
                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white text-white dark:text-gray-8000 dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                Welcome, Admin!
            </div>
        </div>
    </div>
</x-admin-layout>
