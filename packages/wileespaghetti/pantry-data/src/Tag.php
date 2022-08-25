<?php
declare(strict_types=1);

namespace Pantry;

use App\Bookmark;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pantry\Traits\HasUser;

class Tag extends Model
{
    use HasFactory, HasUser;

    protected $fillable = ['name', 'color'];

    /**
     * Get the user that owns the phone.
     */
    public function bookmark(): BelongsTo
    {
        return $this->belongsTo(Bookmark::class);
    }
}
