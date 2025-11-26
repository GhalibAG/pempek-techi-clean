<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    // Paksa layout menjadi 3 kolom
    public function getColumns(): int|string|array
    {
        return 3;
    }
}
