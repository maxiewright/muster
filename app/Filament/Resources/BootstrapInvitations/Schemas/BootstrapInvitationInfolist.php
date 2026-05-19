<?php

declare(strict_types=1);

namespace App\Filament\Resources\BootstrapInvitations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BootstrapInvitationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('email'),
                TextEntry::make('role')
                    ->badge(),
                TextEntry::make('expires_at')
                    ->dateTime(),
                TextEntry::make('accepted_at')
                    ->dateTime()
                    ->placeholder('Pending'),
                TextEntry::make('token')
                    ->label('Setup URL')
                    ->state(fn ($record): string => route('setup', $record))
                    ->copyable(),
            ]);
    }
}
