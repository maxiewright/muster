<?php

declare(strict_types=1);

namespace App\Filament\Resources\BootstrapInvitations;

use App\Filament\Resources\BootstrapInvitations\Pages\CreateBootstrapInvitation;
use App\Filament\Resources\BootstrapInvitations\Pages\ListBootstrapInvitations;
use App\Filament\Resources\BootstrapInvitations\Pages\ViewBootstrapInvitation;
use App\Filament\Resources\BootstrapInvitations\Schemas\BootstrapInvitationForm;
use App\Filament\Resources\BootstrapInvitations\Schemas\BootstrapInvitationInfolist;
use App\Filament\Resources\BootstrapInvitations\Tables\BootstrapInvitationsTable;
use App\Models\TeamInvitation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BootstrapInvitationResource extends Resource
{
    protected static ?string $model = TeamInvitation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    public static function form(Schema $schema): Schema
    {
        return BootstrapInvitationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BootstrapInvitationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BootstrapInvitationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('kind', TeamInvitation::KIND_BOOTSTRAP);
    }

    public static function getNavigationLabel(): string
    {
        return 'Organization Invites';
    }

    public static function getModelLabel(): string
    {
        return 'Organization Invite';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Organization Invites';
    }

    public static function getNavigationGroup(): string
    {
        return 'Provisioning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBootstrapInvitations::route('/'),
            'create' => CreateBootstrapInvitation::route('/create'),
            'view' => ViewBootstrapInvitation::route('/{record}'),
        ];
    }
}
