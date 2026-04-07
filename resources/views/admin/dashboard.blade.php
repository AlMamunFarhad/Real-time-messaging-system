<x-admin-layout>
    <x-slot name="header">
        <h3 class="flex justify-end items-end">
            <a href="{{ route('admin.logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="text-white dark:text-gray-50 flex items-end space-x-1 btn">
                {{ __('Logout') }}
            </a>
        </h3>
        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="hidden">
            @csrf
        </form>
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
