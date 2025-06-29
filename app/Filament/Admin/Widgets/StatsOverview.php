<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Medicine;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Patients', Patient::count())
                ->description('Registered patients')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Active Prescriptions', Prescription::where('prescription_status', '!=', 'completed')->count())
                ->description('Pending prescriptions')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make('Low Stock Medicines', Medicine::where('stock', '<', 10)->count())
                ->description('Medicines below 10 units')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Monthly Revenue', Number::currency(
                Invoice::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('grand_total'), 'IDR'
            ))
                ->description('Current month earnings')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Today\'s Prescriptions', Prescription::whereDate('created_at', today())->count())
                ->description('New prescriptions today')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('info'),

            Stat::make('Pending Payments', Prescription::where('payment_status', 'pending')->count())
                ->description('Unpaid prescriptions')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('warning'),
        ];
    }
} 