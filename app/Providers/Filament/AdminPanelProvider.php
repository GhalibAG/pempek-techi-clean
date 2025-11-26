<?php

namespace App\Providers\Filament;

use App\Filament\Pages\FinancialReport;
use App\Filament\Widgets\ExpensesChart;
use App\Filament\Widgets\LatestTransactions;
use App\Filament\Widgets\LowStockAlert;
use App\Filament\Widgets\ProfitLossChart;
use App\Filament\Widgets\StatsOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
            ->colors([
                'primary' => Color::Red,
            ])

            ->renderHook(
                'panels::sidebar.footer',
                fn () => view('filament.custom-sidebar-footer')
            )

            // 1. HOOK UNTUK TOMBOL LOGOUT DI BAWAH SIDEBAR
            ->renderHook(
                'panels::head.end',
                fn () => '<style>
                    /* 1. SIDEBAR: Paksa Putih (Clean Look) */
                    aside.fi-sidebar {
                        background-color: #ffffff !important; 
                        border-right: 1px solid #e5e7eb !important;
                    }

                    /* 2. LATAR UTAMA: Abu Kalem (Gray-100) */
                    .fi-layout, main, .fi-page-content {
                        background-color: #f3f4f6 !important;
                    }

                    /* 3. Menu AKTIF: Highlight Abu Tipis dengan Teks Merah */
                    .fi-sidebar-item-button[aria-current="page"] {
                        background-color: #e5e7eb !important; /* Gray-200 */
                        color: var(--primary-600) !important; /* Ambil warna Red Primary */
                    }

                    /* 4. Teks & Header Sidebar */
                    .fi-sidebar-header {
                        background-color: #ffffff !important;
                        border-bottom: 1px solid #e5e7eb;
                    }
                </style>'
            )

            // 2. HOOK UNTUK DESIGN CSS (SIDEBAR MERAH LEMBUT)

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                FinancialReport::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // StatsOverview::class, // (Opsional: Matikan ini kalau double sama dashboard)
                ProfitLossChart::class,
                LowStockAlert::class,
                ExpensesChart::class,
                LatestTransactions::class,
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
            ]);
    }
}
