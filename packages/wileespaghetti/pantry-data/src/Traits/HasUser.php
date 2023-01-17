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

    /*
     * TODO
     * scopeByUser()?
     * https://elishaukpongson.medium.com/laravel-scope-an-introduction-87ec5acc39e
     * https://www.larashout.com/using-scopes-in-laravel
     * https://github.com/Kovah/LinkAce/pull/468/files
     */
}
