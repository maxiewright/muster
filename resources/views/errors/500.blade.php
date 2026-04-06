@extends('layouts.error')

@section('content')
    <div class="flex flex-col items-center gap-5 text-center">
        <p class="text-xs uppercase tracking-[0.2em] text-slate-400 dark:text-zinc-400">Error 500</p>
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-zinc-50">Server error</h1>
        <p class="max-w-sm text-sm text-slate-600 dark:text-zinc-300">Something went wrong. We have been notified and are working on it.</p>
        <a href="{{ route('home') }}" class="inline-flex min-h-[44px] items-center rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:border-zinc-500 dark:hover:bg-zinc-900" wire:navigate>
            Back to home
        </a>
    </div>
@endsection
