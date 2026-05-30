<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class DailyMusterForm extends Form
{
    #[Validate('nullable|string|max:1000')]
    public string $blockers = '';

    #[Validate('nullable|string|in:firing,steady,strong,struggling,blocked')]
    public ?string $mood = null;

    #[Validate('required|string|max:255', as: 'task title')]
    public string $newTaskTitle = '';
}
