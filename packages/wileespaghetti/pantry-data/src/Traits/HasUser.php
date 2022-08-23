<?php
declare(strict_types=1);

namespace Pantry\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pantry\User;

trait HasUser {
    /**
     * Get the user that owns this.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
