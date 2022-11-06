<?php
declare(strict_types=1);

namespace Pantry;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Pantry\Traits\HasUser;

class Bookmark extends Model
{
    use HasUser;
    use HasFactory;

    protected $fillable = ['name', 'image', 'url', 'description', 'public', 'created_at', 'updated_at'];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
