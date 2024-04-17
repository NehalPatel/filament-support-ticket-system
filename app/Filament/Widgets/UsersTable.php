<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UsersTable extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()->latest()->take(2)
            )
            ->columns([
                TextColumn::make('name')
                    ->sortable(),
                TextColumn::make('email')
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->badge(),
            ]);
    }
}
