<?php

use Carbon\CarbonImmutable;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public ?string $selectedDate = null;

    public ?int $eventId = null;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('required|date')]
    public string $starts_at = '';

    #[Validate('nullable|date|after:starts_at')]
    public ?string $ends_at = null;

    #[Validate('required|string')]
    public string $type = 'huddle';

    #[Validate('array')]
    public array $participants = [];

    public function mount(?string $selectedDate = null, ?int $eventId = null): void
    {
        if ($eventId) {
            $event = \App\Models\Event::with('type')->findOrFail($eventId);
            $this->eventId = $event->id;
            $this->title = $event->title;
            $this->description = $event->description ?? '';
            $this->starts_at = $event->starts_at->format('Y-m-d\TH:i');
            $this->ends_at = $event->ends_at?->format('Y-m-d\TH:i');
            $this->type = match ($event->type->slug) {
                'huddle' => 'huddle',
                'training-session' => 'training',
                'pair-programming' => 'pairing',
                'code-review' => 'review',
                default => 'huddle',
            };

            return;
        }

        if ($selectedDate) {
            $this->starts_at = $selectedDate.'T09:00';
        } else {
            $this->starts_at = now()->format('Y-m-d\TH:i');
        }

        $this->ends_at = CarbonImmutable::parse($this->starts_at)
            ->addHour()
            ->format('Y-m-d\TH:i');
    }

    public function updatedStartsAt(string $value): void
    {
        if (! $this->ends_at) {
            $this->ends_at = CarbonImmutable::parse($value)->addHour()->format('Y-m-d\TH:i');

            return;
        }

        if (CarbonImmutable::parse($this->ends_at)->lessThanOrEqualTo(CarbonImmutable::parse($value))) {
            $this->ends_at = CarbonImmutable::parse($value)->addHour()->format('Y-m-d\TH:i');
        }
    }

    public function save(): void
    {
        $this->validate();

        /** @var \App\Models\EventType $eventType */
        $eventType = \App\Models\EventType::where('slug', $this->eventTypeSlug())->firstOrFail();

        $data = [
            'user_id' => auth()->id(),
            'event_type_id' => $eventType->id,
            'title' => $this->title,
            'description' => $this->description ?: null,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at ?: null,
            'color' => $eventType->color,
        ];

        if ($this->eventId) {
            \App\Models\Event::findOrFail($this->eventId)->update($data);
        } else {
            \App\Models\Event::create($data);
        }

        $this->dispatch('saved');
        $this->dispatch('close');
    }

    private function eventTypeSlug(): string
    {
        return match ($this->type) {
            'huddle' => 'huddle',
            'training' => 'training-session',
            'pairing' => 'pair-programming',
            'review' => 'code-review',
            default => 'huddle',
        };
    }

    #[Computed]
    public function teamMembers()
    {
        return \App\Models\User::orderBy('name')->get();
    }

    public function render()
    {
        return view('components.calendar.⚡create-event-modal.create-event-modal');
    }
};
