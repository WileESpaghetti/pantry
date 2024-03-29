<?php

declare(strict_types=1);

namespace Pantry\Models;

use Database\Factories\FolderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Pantry\Traits\HasUser;

class Folder extends Model
{
    use HasFactory, HasUser;

    /**
     * `color` and `links` can be filled only because they are needed for the Larder API
     */
    protected $fillable = ['name', 'color', 'links', 'user_id'];

    /**
     * Get the bookmarks for the folder.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    protected static function newFactory(): FolderFactory
    {
        return new FolderFactory;
    }
}
