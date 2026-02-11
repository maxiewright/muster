<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        <div class="min-h-[100dvh] w-full p-4 sm:p-6 lg:p-8">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts::app.sidebar>
