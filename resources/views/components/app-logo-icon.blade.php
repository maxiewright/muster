<img
    src="{{ asset('logo.svg') }}"
    alt="{{ config('app.name', 'Muster') }} logo"
    decoding="async"
    loading="eager"
    {{ $attributes->merge(['class' => 'h-full w-full object-contain']) }}
/>
