<?php

namespace HtmlBookmarks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pantry\Traits\HasUser;

/**
 * Metadata for imported HTML bookmark files
 */
class BookmarkFile extends Model
{
    use HasFactory, HasUser;

    protected $fillable = [
        'file_name',
        'file_name_original',
        'sha256sum',
        'file_size_bytes',
        'path',
    ];

    protected $hidden = [
        'path', // do not leak where the server stores the bookmark files
    ];
}