<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        return [
            Stat::make('Total Tickets', Ticket::count()),
            Stat::make('Total Categories', Category::where('is_active', true)->count()),
            Stat::make('Total Agents', User::whereHas('roles', function (Builder $query){
                $query->where('name', Role::ROLES['Agent']);
            }) ->count()),
        ];
    }
}
