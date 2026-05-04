<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'avatar',
        'is_admin',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
        ];
    }

    // ── FilamentUser contract ─────────────────────────────────────────────────

    /**
     * Hanya user dengan is_admin = true yang bisa mengakses panel /admin.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Order yang dikonfirmasi oleh user ini sebagai admin. */
    public function confirmedPayments(): HasMany
    {
        return $this->hasMany(PaymentConfirmation::class, 'confirmed_by');
    }

    /** Log perubahan status order yang dilakukan oleh user ini. */
    public function orderStatusChanges(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class, 'changed_by');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * URL avatar — kembalikan placeholder jika belum di-upload.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return \Storage::disk('r2')->url($this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=f97316&color=fff';
    }
}
