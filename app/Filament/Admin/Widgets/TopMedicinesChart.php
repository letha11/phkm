<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Medicine;
use App\Models\PrescriptionItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopMedicinesChart extends ChartWidget
{
    protected static ?string $heading = 'Most Prescribed Medicines (Top 10)';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $topMedicines = PrescriptionItem::select('medicine_id', DB::raw('SUM(medicine_amount_prescribed) as total_quantity'))
            ->with('medicine:id,name')
            ->groupBy('medicine_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Prescribed',
                    'data' => $topMedicines->pluck('total_quantity')->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                        'rgba(255, 99, 255, 0.8)',
                        'rgba(99, 255, 132, 0.8)',
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(255, 99, 255, 1)',
                        'rgba(99, 255, 132, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $topMedicines->pluck('medicine.name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
} 