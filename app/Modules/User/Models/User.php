<?php

declare(strict_types=1);

namespace App\Modules\User\Models;

use App\Modules\Auth\Notifications\ResetPasswordNotification;
use App\Modules\Auth\Notifications\VerifyEmailNotification;
use App\Modules\Friendship\Models\Friendship;
use App\Modules\Notification\Models\Notification;
use App\Modules\Post\Models\Comment;
use App\Modules\Post\Models\Post;
use App\Modules\Post\Models\Reaction;
use App\Modules\Reel\Models\Reel;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

/**
 * Core identity + authentication entity. Extended (public-facing) profile
 * data lives in the related Profile model to keep this table lean.
 */
class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'gender',
        'email_verified_at',
        'last_active_at',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_active_at'    => 'datetime',
            'date_of_birth'     => 'date',
            'is_active'         => 'boolean',
            'password'          => 'hashed',
        ];
    }

    // ---------------------------------------------------------------------
    // JWTSubject
    // ---------------------------------------------------------------------

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /** @return array<string, mixed> */
    public function getJWTCustomClaims(): array
    {
        // Lightweight claims surfaced in the token for the client to read.
        return [
            'username' => $this->username,
            'name'     => $this->name,
        ];
    }

    // ---------------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------------

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function reels(): HasMany
    {
        return $this->hasMany(Reel::class);
    }

    /** Friendships this user initiated. */
    public function sentFriendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'requester_id');
    }

    /** Friendships addressed to this user. */
    public function receivedFriendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'addressee_id');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'recipient_id');
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    public function avatarUrl(): ?string
    {
        return $this->profile?->avatar_url;
    }

    // ---------------------------------------------------------------------
    // Notification overrides (use Nexora-branded, queued mail)
    // ---------------------------------------------------------------------

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
