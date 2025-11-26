<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser; // <-- PENTING
use Filament\Panel; // <-- PENTING

// Tambahkan "implements FilamentUser" di sini
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- FUNGSI IZIN MASUK FILAMENT (WAJIB ADA DI PRODUCTION) ---
    public function canAccessPanel(Panel $panel): bool
    {
        // Izinkan masuk jika role adalah 'admin' atau 'owner'
        // Atau kembalikan 'true' jika ingin mengizinkan semua user login (untuk testing)
        return $this->role === 'admin' || $this->role === 'owner';
    }
}