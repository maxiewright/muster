<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Enums\TaskStatus;
use App\Models\PartnerNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PartnerNotificationsDropdown extends Component
{
    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        $userId = Auth::id();
        if (! is_int($userId)) {
            return [];
        }

        $channel = "echo-private:App.Models.User.{$userId}";

        return [
            "{$channel},TrainingCheckinLogged" => 'onTrainingCheckinLogged',
            "{$channel},TaskAssigned" => 'onTaskAssigned',
            "{$channel},TaskStatusChanged" => 'onTaskStatusChanged',
        ];
    }

    #[Computed]
    public function notifications()
    {
        return Auth::user()->partnerNotifications()
            ->with(['fromUser', 'goal'])
            ->latest()
            ->take(10)
            ->get();
    }

    #[Computed]
    public function unreadCount()
    {
        return Auth::user()->partnerNotifications()->whereNull('read_at')->count();
    }

    public function markAsRead(int $id): void
    {
        $notification = PartnerNotification::findOrFail($id);
        if ($notification->user_id === Auth::id()) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->partnerNotifications()->whereNull('read_at')->update(['read_at' => now()]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function onTrainingCheckinLogged(array $payload): void
    {
        unset($this->notifications, $this->unreadCount);

        $fromUser = is_string($payload['from_user_name'] ?? null) ? $payload['from_user_name'] : 'Your partner';
        $goalTitle = is_string($payload['goal_title'] ?? null) ? trim((string) $payload['goal_title']) : '';
        $summary = is_string($payload['message'] ?? null) ? trim((string) $payload['message']) : '';

        $message = $goalTitle !== ''
            ? "{$fromUser} checked in on \"{$goalTitle}\"."
            : "{$fromUser} logged a new training check-in.";

        if ($summary !== '') {
            $message .= " {$summary}";
        }

        $this->showToast('Training update', $message, 'success');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function onTaskAssigned(array $payload): void
    {
        $task = $payload['task'] ?? [];
        $title = is_string($task['title'] ?? null) ? $task['title'] : 'A task';
        $creator = is_string($task['creator_name'] ?? null) ? $task['creator_name'] : 'A teammate';

        $this->showToast('New task assigned', "{$creator} assigned \"{$title}\" to you.", 'success');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function onTaskStatusChanged(array $payload): void
    {
        $task = $payload['task'] ?? [];
        $title = is_string($task['title'] ?? null) ? $task['title'] : 'A task';
        $changedBy = is_string($payload['changed_by'] ?? null) ? $payload['changed_by'] : 'A teammate';
        $toStatus = TaskStatus::tryFrom((string) ($payload['to_status'] ?? ''));
        $statusLabel = $toStatus?->label() ?? 'Updated';

        $heading = match ($toStatus) {
            TaskStatus::Completed => 'Task completed',
            TaskStatus::Blocked => 'Task blocked',
            default => 'Task status updated',
        };

        $variant = match ($toStatus) {
            TaskStatus::Completed => 'success',
            TaskStatus::Blocked => 'danger',
            default => 'warning',
        };

        $this->showToast($heading, "{$changedBy} changed \"{$title}\" to {$statusLabel}.", $variant);
    }

    protected function showToast(string $heading, string $message, string $variant): void
    {
        $this->dispatch(
            'toast-show',
            duration: 6000,
            slots: ['heading' => $heading, 'text' => $message],
            dataset: ['variant' => $variant]
        );
    }

    public function render()
    {
        return view('livewire.training.partner-notifications-dropdown');
    }
}
