<?php

declare(strict_types=1);

namespace Pantry;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pantry\Traits\HasUser;

// TODO evaluate the spatie laravel-tags package to see if it a better solution than having to be custom
class Tag extends Model
{
    use HasFactory, HasUser;

    protected $fillable = ['name', 'color'];

    protected static function newFactory(): TagFactory
    {
        return new TagFactory;
    }
}
