<?php

namespace App\Filament\Resources;

use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Tables;
use App\Models\Ticket;
use Filament\Forms\Form;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextInputColumn;
use App\Filament\Resources\TicketResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TicketResource\RelationManagers\CategoriesRelationManager;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                TextInput::make('title')
                    ->autofocus()
                    ->required(),
                Textarea::make('description')
                    ->rows(3),
                Select::make('status')
                    ->options(self::$model::STATUS)
                    ->required()
                    ->in(self::$model::STATUS),
                Select::make('priority')
                    ->options(Ticket::PRIORITY)
                    ->required()
                    ->in(self::$model::PRIORITY),
                Select::make('assigned_to')
                    ->options(
                        User::whereHas('roles', function (Builder $query) {
                            $query->where('name', Role::ROLES['Agent']);
                        })->pluck('name', 'id')->toArray()
                    ),
                    // ->relationship('assignedTo', 'name'),
                Textarea::make('comment')
                    ->rows(3),
                FileUpload::make('attachment')
                    ->multiple()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->description(fn(Ticket $record) : ?string => $record?->description ?? null)
                    ->sortable()
                    ->searchable(),
                SelectColumn::make('status')
                    ->options(Ticket::STATUS),
                // TextColumn::make('status')
                //     ->badge()
                //     ->colors([
                //         'warning' => self::$model::STATUS['Archived'],
                //         'success' => self::$model::STATUS['Open'],
                //         'danger' => self::$model::STATUS['Closed'],
                //     ]),
                TextColumn::make('priority')
                    ->badge()
                    ->colors([
                        'warning' => self::$model::PRIORITY['Medium'],
                        'success' => self::$model::PRIORITY['Low'],
                        'danger' => self::$model::PRIORITY['High'],
                    ]),
                TextColumn::make('assignedTo.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignedBy.name')
                    ->searchable()
                    ->sortable(),
                TextInputColumn::make('comment')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->disabled(!auth()->user()->hasPermission('ticket_edit')),
                TextColumn::make('created_at')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->dateTime()
                    ->sortable()
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder =>
                auth()->user()->hasRole(Role::ROLES['Admin']) ? $query : $query->where('assigned_to', auth()->id())
            ) //load all tickets if user has Admin role
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::$model::STATUS),
                Tables\Filters\SelectFilter::make('priority')
                    ->options(self::$model::PRIORITY),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->hidden(!auth()->user()->hasPermission('ticket_delete')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CategoriesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
