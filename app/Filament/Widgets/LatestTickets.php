<?php

namespace App\Filament\Widgets;

use App\Models\Role;
use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTickets extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                auth()->user()->hasRole(Role::ROLES['Admin']) ? Ticket::query() : Ticket::where('assigned_to', auth()->id())
            )
            ->columns([
                TextColumn::make('title')
                    ->description(fn(Ticket $record) : ?string => $record?->description ?? null)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => Ticket::STATUS['Archived'],
                        'success' => Ticket::STATUS['Open'],
                        'danger' => Ticket::STATUS['Closed'],
                    ]),
                TextColumn::make('priority')
                    ->badge()
                    ->colors([
                        'warning' => Ticket::PRIORITY['Medium'],
                        'success' => Ticket::PRIORITY['Low'],
                        'danger' => Ticket::PRIORITY['High'],
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
            ->defaultSort('created_at', 'desc');
    }
}
