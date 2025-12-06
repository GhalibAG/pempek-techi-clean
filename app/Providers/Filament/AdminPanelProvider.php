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
            ->passwordReset()
            ->darkMode(false)
            ->colors([
                'primary' => Color::Red,
            ])

            ->brandName(false) // Ini mematikan tulisan "Laravel"
            ->brandLogo(fn () => view('filament.logo')) // Ini memanggil logo kamu
            ->brandLogoHeight('3rem')

            ->renderHook(
                'panels::sidebar.footer',
                fn () => view('filament.custom-sidebar-footer')
            )

            // 1. HOOK UNTUK TOMBOL LOGOUT DI BAWAH SIDEBAR
            ->renderHook(
                'panels::head.end',
                fn () => '<style>
                   .fi-section, .fi-widget, .fi-ta {
                        background-color: #ffffff !important;
                        border-radius: 0.75rem !important; /* Sudut membulat */
                        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1) !important; /* Shadow tipis */
                        border: 1px solid #e5e7eb !important; /* Border abu tipis */
                    }

                    /* 2. Header Widget (Judul) lebih tegas */
                    .fi-section-header-heading, .fi-widget-header-heading {
                        font-weight: 700 !important;
                        color: #111827 !important; /* Hitam pekat */
                    }

                    /* 3. Berikan jarak antar widget */
                   .fi-widget {
                     border-top: 4px solid var(--primary-500) !important; /* Garis Merah di atas */
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
