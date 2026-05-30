<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identity')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Full name')
                            ->placeholder('e.g. Cpl Aaron Joseph')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),

                Section::make('Access')
                    ->description('Account credentials and platform-wide permissions.')
                    ->columns(2)
                    ->components([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                ? 'Set an initial password the member can change after first sign-in.'
                                : 'Leave blank to keep the current password.')
                            ->maxLength(255),
                        Select::make('role')
                            ->label('Account role')
                            ->options(collect(Role::cases())->mapWithKeys(fn (Role $role): array => [$role->value => $role->label()])->all())
                            ->required()
                            ->native(false)
                            ->helperText('Lead can manage teams; Member is a standard account.'),
                        Toggle::make('is_platform_admin')
                            ->label('Platform administrator')
                            ->helperText('Grants access to this admin panel across every organization.')
                            ->inline(false)
                            ->columnSpanFull(),
                    ]),

                Section::make('Membership')
                    ->description('Top-level organization. Unit memberships are managed separately.')
                    ->components([
                        Select::make('organization_id')
                            ->label('Organization')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Unassigned'),
                    ]),
            ]);
    }
}
