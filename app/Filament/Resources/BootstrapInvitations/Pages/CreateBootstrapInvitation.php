<?php

declare(strict_types=1);

namespace App\Filament\Resources\BootstrapInvitations\Pages;

use App\Enums\Role;
use App\Filament\Resources\BootstrapInvitations\BootstrapInvitationResource;
use App\Mail\BootstrapInvitationMail;
use App\Models\TeamInvitation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateBootstrapInvitation extends CreateRecord
{
    protected static string $resource = BootstrapInvitationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            ...$data,
            'kind' => TeamInvitation::KIND_BOOTSTRAP,
            'organization_id' => null,
            'unit_id' => null,
            'invited_by_user_id' => auth()->id(),
            'role' => Role::Lead->value,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ];
    }

    protected function afterCreate(): void
    {
        Mail::to($this->record->email)->queue(new BootstrapInvitationMail($this->record));
    }
}
