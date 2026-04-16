@php
    $isAdmin = $currentParticipantTypeShort === 'admin';
@endphp

@if ($isAdmin)
    <x-admin-layout>
        <x-slot name="header">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Messaging Workspace</h2>
                <p class="mt-1 text-sm text-gray-500">Direct chat and group chat in one place.</p>
            </div>
        </x-slot>

        @include('messaging::chat.partials.dashboard-content')
    </x-admin-layout>
@else
    @component('layouts.app')
        @slot('header')
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Messaging Workspace</h2>
                <p class="mt-1 text-sm text-gray-500">Talk to admin and collaborate in groups.</p>
            </div>
        @endslot

        @include('messaging::chat.partials.dashboard-content')
    @endcomponent
@endif
