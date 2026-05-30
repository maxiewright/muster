<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Enums\Role;
use App\Models\Organization;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Member')
                    ->weight('medium')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record): string => (string) $record->email),
                TextColumn::make('organization.name')
                    ->label('Organization')
                    ->placeholder('—')
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (Role $state): string => match ($state) {
                        Role::Lead => 'amber',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (Role $state): string => $state->label())
                    ->sortable(),
                IconColumn::make('is_platform_admin')
                    ->label('Admin')
                    ->boolean()
                    ->trueColor('amber')
                    ->falseIcon('')
                    ->alignCenter(),
                TextColumn::make('points')
                    ->label('Points')
                    ->numeric()
                    ->alignEnd()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('current_streak')
                    ->label('Streak')
                    ->formatStateUsing(fn (int $state): string => $state.' d')
                    ->alignEnd()
                    ->color('gray')
                    ->sortable(),
                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->getStateUsing(fn ($record): bool => $record->email_verified_at !== null)
                    ->boolean()
                    ->trueColor('emerald')
                    ->falseColor('gray')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('organization_id')
                    ->label('Organization')
                    ->options(fn (): array => Organization::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('role')
                    ->label('Role')
                    ->options(collect(Role::cases())->mapWithKeys(fn (Role $role): array => [$role->value => $role->label()])->all()),
                TernaryFilter::make('is_platform_admin')
                    ->label('Platform admins only')
                    ->placeholder('All members')
                    ->trueLabel('Admins only')
                    ->falseLabel('Non-admins only'),
            ])
            ->emptyStateHeading('No members yet')
            ->emptyStateDescription('Invite a member from the Organization Invites screen to populate this list.')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
