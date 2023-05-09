<?php
declare(strict_types=1);

namespace Pantry\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasUser {
    /**
     * Get the user that owns this.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
