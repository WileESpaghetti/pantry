<?php
declare(strict_types=1);

namespace Pantry\Models;

use Database\Factories\BookmarkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Pantry\Traits\HasUser;

// TODO may want to add a `getDisplayName()` function to encapsulate showing URL if the title is not specified
class Bookmark extends Model
{
    use HasFactory, HasUser;

    protected $fillable = ['name', 'image', 'url', 'description', 'public', 'created_at', 'updated_at', 'user_id'];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    protected static function newFactory(): BookmarkFactory
    {
        return new BookmarkFactory;
    }
}
