<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TicketsOverviewChart extends ChartWidget
{
    protected static ?string $heading = 'Tickets Overview';

    public ?string $filter = 'week';

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        $start = null;
        $end = null;
        $interval = null;

        switch ($activeFilter) {
            case 'week':
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
                $interval = 'perDay';
                break;
            case 'month':
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                $interval = 'perDay';
                break;
            case 'year':
                $start = now()->startOfYear();
                $end = now()->endOfYear();
                $interval = 'perMonth';
                break;
            default:
                break;
        }

        $data = Trend::model(Ticket::class)
            ->between(
                start: $start,
                end: $end,
            )
            ->$interval()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Tickets Data',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
