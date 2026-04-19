@php
    $isAdmin = $currentParticipantTypeShort === 'admin';
@endphp

@if ($isAdmin)
    <x-admin-layout>
        <x-slot name="header">
            <div></div>
        </x-slot>

        @include('messaging::chat.partials.dashboard-content')
    </x-admin-layout>
@else
    @component('layouts.app')
        @slot('header')
            <div>
            </div>
        @endslot

        @include('messaging::chat.partials.dashboard-content')
    @endcomponent
@endif
