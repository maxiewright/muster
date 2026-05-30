<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrganizationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount(['units', 'users']))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Organization')
                    ->weight('medium')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record): string => $record->slug),
                TextColumn::make('units_count')
                    ->label('Units')
                    ->badge()
                    ->color('amber')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('Members')
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Provisioned')
                    ->since()
                    ->sortable(),
            ])
            ->emptyStateHeading('No organizations yet')
            ->emptyStateDescription('Create the first organization to start onboarding units and members.')
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
