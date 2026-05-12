<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'strava_id',
        'name',
        'email',
        'avatar',
        'strava_access_token',
        'strava_refresh_token',
        'strava_token_expires_at',
    ];

    protected $hidden = [
        'strava_access_token',
        'strava_refresh_token',
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'strava_token_expires_at' => 'datetime',
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function isStravaTokenExpired(): bool
    {
        return $this->strava_token_expires_at === null
            || now()->gte($this->strava_token_expires_at);
    }
}
