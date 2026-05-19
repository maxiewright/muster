<?php

declare(strict_types=1);

namespace App\Filament\Resources\BootstrapInvitations\Pages;

use App\Filament\Resources\BootstrapInvitations\BootstrapInvitationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBootstrapInvitations extends ListRecords
{
    protected static string $resource = BootstrapInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
