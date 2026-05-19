@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Muster" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center">
            <x-app-logo-icon />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Muster" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center">
            <x-app-logo-icon />
        </x-slot>
    </flux:brand>
@endif
