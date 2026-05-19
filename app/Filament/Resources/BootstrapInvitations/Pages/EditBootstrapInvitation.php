<?php

declare(strict_types=1);

namespace App\Filament\Resources\BootstrapInvitations\Pages;

use App\Filament\Resources\BootstrapInvitations\BootstrapInvitationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBootstrapInvitation extends EditRecord
{
    protected static string $resource = BootstrapInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
