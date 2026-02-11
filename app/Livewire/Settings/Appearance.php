<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Appearance extends Component
{
    public string $theme = 'system';

    public function mount(): void
    {
        $this->theme = Auth::user()->theme ?? 'system';
    }

    public function updateTheme(): void
    {
        $this->validate([
            'theme' => ['required', Rule::in(['light', 'dark', 'system'])],
        ]);

        Auth::user()->update([
            'theme' => $this->theme,
        ]);

        $this->dispatch('theme-updated');
    }

    public function render(): mixed
    {
        return view('livewire.settings.appearance');
    }
}
