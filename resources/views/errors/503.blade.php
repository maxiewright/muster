@extends('layouts.error')

@section('content')
    <div class="flex flex-col items-center gap-4 text-center">
        <p class="text-6xl font-semibold text-zinc-400 dark:text-zinc-500">503</p>
        <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200">Be right back</h1>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">We are performing scheduled maintenance. Please try again in a few minutes.</p>
        <a href="{{ route('home') }}" class="text-sm font-medium text-zinc-800 underline dark:text-zinc-200" wire:navigate>Back to home</a>
    </div>
@endsection
