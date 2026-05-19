<?php

use App\Models\Event;
use App\Models\EventType;
use App\Models\Unit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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
            $event = Event::with('type')
                ->inUnit(auth()->user()?->activeUnitId())
                ->findOrFail($eventId);
            $this->authorize('update', $event);
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

        $this->starts_at = $selectedDate ? $selectedDate.'T09:00' : now()->format('Y-m-d\TH:i');

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

        /** @var EventType $eventType */
        $eventType = EventType::where('slug', $this->eventTypeSlug())->firstOrFail();

        $data = [
            'organization_id' => auth()->user()?->activeUnit()?->organization_id,
            'unit_id' => auth()->user()?->activeUnitId(),
            'user_id' => auth()->id(),
            'event_type_id' => $eventType->id,
            'title' => $this->title,
            'description' => $this->description ?: null,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at ?: null,
            'color' => $eventType->color,
        ];

        if ($this->eventId) {
            $event = Event::query()
                ->inUnit(auth()->user()?->activeUnitId())
                ->findOrFail($this->eventId);
            $this->authorize('update', $event);
            $event->update($data);
        } else {
            Event::create($data);
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
        $activeUnit = auth()->user()?->activeUnit();

        if ($activeUnit instanceof Unit) {
            return $activeUnit->users()->orderBy('name')->get();
        }

        return User::orderBy('name')->get();
    }

    public function render(): Factory|View
    {
        return view('components.calendar.⚡create-event-modal.create-event-modal');
    }
};
