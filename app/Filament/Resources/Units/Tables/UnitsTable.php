<?php

declare(strict_types=1);

namespace App\Filament\Resources\Units\Tables;

use App\Models\Organization;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount(['memberships', 'missions']))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Unit')
                    ->weight('medium')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record): string => $record->slug),
                TextColumn::make('organization.name')
                    ->label('Organization')
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('memberships_count')
                    ->label('Members')
                    ->badge()
                    ->color('amber')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('missions_count')
                    ->label('Missions')
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('organization_id')
                    ->label('Organization')
                    ->options(fn (): array => Organization::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
            ])
            ->emptyStateHeading('No units yet')
            ->emptyStateDescription('Create a unit under an organization to start onboarding members.')
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
