<?php

declare(strict_types=1);

namespace App\Filament\Resources\BootstrapInvitations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BootstrapInvitationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Organization Commander Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->placeholder('commander@example.mil'),
            ]);
    }
}
