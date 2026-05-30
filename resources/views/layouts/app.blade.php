<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{-- Mobile cancels flux:main's default p-6 (24px) via negative margins, then re-adds a tight
             12px gutter via px-3. Cards are nearly edge-to-edge on phones; desktop keeps flux:main's
             natural breathing room because the sidebar is visible. --}}
        <div class="-mx-6 -mt-6 px-3 pt-3 pb-24 sm:m-0 sm:p-0 sm:pb-0">
            <div class="space-y-4 sm:space-y-6">
                {{ $slot }}
            </div>
        </div>
    </flux:main>
</x-layouts::app.sidebar>
