<?php

namespace HtmlBookmarks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pantry\Traits\HasUser;

class BookmarkFileImport extends Model // FIXME rename to HtmlFileImport
{
    use HasFactory, HasUser;

    protected $fillable = [
        'bookmark_file_id',
        'finished_at',
        'started_at',
        'status',
        'user_id'
    ];

    public function bookmarkFile(): BelongsTo {
        return $this->belongsTo(BookmarkFile::class);
    }
}
