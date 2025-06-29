<?php

namespace App\Filament\Pages;

use App\Filament\Admin\Widgets\StatsOverview;
use App\Filament\Admin\Widgets\SimpleRevenueChart;

use App\Filament\Admin\Widgets\TopMedicinesChart;
use App\Filament\Admin\Widgets\LowStockTable;
use App\Filament\Admin\Widgets\RecentActivity;

class Dashboard extends \Filament\Pages\Dashboard
{
    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            SimpleRevenueChart::class,
            TopMedicinesChart::class,
            LowStockTable::class,
            RecentActivity::class,
        ];
    }
}
