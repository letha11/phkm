<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Models\User;
use App\Filament\Pages\Dashboard;

class AdminPanelProvider extends PanelProvider
{

    public static function getUrl(): string
    {
        return url('admin');
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->brandName('MedEase Admin')
            ->brandLogo(asset('assets/images/logo-vertical.png'))
            ->brandLogoHeight('6rem')
            ->colors([
                'primary' => Color::Sky,
            ])
            ->darkMode(false)
            ->font('poppins')
            ->sidebarCollapsibleOnDesktop(true)
            ->navigationGroups([
                'Patient Management',
                'Inventory Management', 
                'Medical Records',
                'Financial Management',
                'User Management',
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                \App\Filament\Admin\Widgets\StatsOverview::class,
                \App\Filament\Admin\Widgets\SimpleRevenueChart::class,
                \App\Filament\Admin\Widgets\TopMedicinesChart::class,
                \App\Filament\Admin\Widgets\LowStockTable::class,
                \App\Filament\Admin\Widgets\RecentActivity::class,
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                'role:' . User::ROLE_ADMIN,
            ]);
    }
}
