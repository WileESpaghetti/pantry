<?php

namespace Pantry;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Nabcellent\Laraconfig\HasConfig;

class User extends Authenticatable
{
    use HasFactory, Notifiable/*, HasConfig*/;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function identities(): HasMany
    {
        return $this->hasMany('App\SocialIdentity');
    }

    /**
     * Get the bookmarks for the user.
     * @return HasMany
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    protected static function newFactory(): UserFactory
    {
        return new UserFactory;
    }
}
