<?php
declare(strict_types=1);

namespace App\Traits;

use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasUser {
    /**
     * Get the user that owns this.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
