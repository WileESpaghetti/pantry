<?php

declare(strict_types=1);

namespace Pantry\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Pantry\Traits\HasUser;

class Tag extends Model
{
    use HasFactory, HasUser;

    protected $fillable = ['name', 'color', 'user_id'];

    /**
     * Get the bookmarks for the tag.
     */
    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(Bookmark::class);
    }

    protected static function newFactory(): TagFactory
    {
        return new TagFactory;
    }
}
