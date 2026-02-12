@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <flux:heading size="xl" class="!text-zinc-50">{{ $title }}</flux:heading>
    <flux:subheading class="!text-zinc-300">{{ $description }}</flux:subheading>
</div>
