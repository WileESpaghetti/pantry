<?php

namespace HtmlBookmarks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Pantry\Traits\HasUser;

/**
 * Metadata for imported HTML bookmark files
 *
 * TODO maybe add disk field
 * TODO maybe add original created/modified dates
 */
class BookmarkFile extends Model
{
    use HasFactory, HasUser;

    protected $fillable = [
        'file_name',
        'file_name_original',
        'file_size_bytes',
        'path',
        'sha256sum',
        'user_id'
    ];

    protected $hidden = [
        // hide stuff that might leak server storage paths
        'path',
        'file_name',
    ];

    public function bookmarkFileImport(): HasMany {
        return $this->hasMany(BookmarkFileImport::class);
    }
}
