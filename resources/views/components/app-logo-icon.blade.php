<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 110" {{ $attributes }}>
    {{-- Shield body --}}
    <path
        fill="currentColor"
        d="M50 5 L90 20 L90 60 C90 82 72 96 50 105 C28 96 10 82 10 60 L10 20 Z"
    />
    {{-- Horizontal lines (standup rows) --}}
    <rect x="22" y="36" width="30" height="6" fill="white" opacity="0.5" rx="2"/>
    <rect x="22" y="50" width="45" height="6" fill="white" opacity="0.5" rx="2"/>
    <rect x="22" y="64" width="22" height="6" fill="white" opacity="0.5" rx="2"/>
    {{-- Blue diagonal arrow line --}}
    <line x1="25" y1="80" x2="78" y2="22" stroke="#3B82F6" stroke-width="7" stroke-linecap="round"/>
    {{-- Blue arrow head --}}
    <polyline points="55,18 79,22 75,44" fill="none" stroke="#3B82F6" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
