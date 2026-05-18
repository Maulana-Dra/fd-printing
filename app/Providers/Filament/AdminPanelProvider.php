<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()

            // ── Branding ──────────────────────────────────────────────────────
            ->brandName(config('printing.company.name', 'FD Printing'))
            ->brandLogo(null)   // Ganti dengan ->brandLogo(asset('images/logo.png')) jika logo ada
            ->favicon(null)

            // ── Colors & Font ─────────────────────────────────────────────────
            ->colors([
                'primary' => Color::Orange,  // Brand percetakan: orange
                'gray'    => Color::Slate,
                'info'    => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger'  => Color::Rose,
            ])
            ->font('Inter')

            // ── UI Options ────────────────────────────────────────────────────
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->breadcrumbs(true)
            ->maxContentWidth(\Filament\Support\Enums\MaxWidth::Full)

            // ── Navigation Groups (ordering) ──────────────────────────────────
            ->navigationGroups([
                NavigationGroup::make('Pesanan')
                    ->icon('heroicon-o-shopping-cart')
                    ->collapsed(false),
                NavigationGroup::make('Katalog')
                    ->icon('heroicon-o-tag')
                    ->collapsed(true),
                NavigationGroup::make('Keuangan')
                    ->icon('heroicon-o-banknotes')
                    ->collapsed(true),
                NavigationGroup::make('Pengaturan')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(true),
            ])

            // ── Resource & Page Discovery ─────────────────────────────────────
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\ReportPage::class,
            ])

            // ── Widget Discovery ──────────────────────────────────────────────
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\StatsOverviewWidget::class,
                \App\Filament\Widgets\RecentOrdersWidget::class,
                \App\Filament\Widgets\PendingPaymentsWidget::class,
            ])

            // ── Global Search ─────────────────────────────────────────────────
            ->globalSearch(true)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])

            // ── Middleware ────────────────────────────────────────────────────
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
            ]);
    }
}
