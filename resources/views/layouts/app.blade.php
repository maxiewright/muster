<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        <div class="min-h-screen w-full p-4 pb-20 sm:p-6 lg:p-8 lg:pb-8">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts::app.sidebar>
