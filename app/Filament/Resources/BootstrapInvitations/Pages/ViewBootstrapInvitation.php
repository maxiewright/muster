<?php

declare(strict_types=1);

namespace App\Filament\Resources\BootstrapInvitations\Pages;

use App\Filament\Resources\BootstrapInvitations\BootstrapInvitationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBootstrapInvitation extends ViewRecord
{
    protected static string $resource = BootstrapInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
