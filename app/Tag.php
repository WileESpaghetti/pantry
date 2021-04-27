<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    /**
     * Get the user that owns the phone.
     */
    public function bookmark()
    {
        return $this->belongsTo(Bookmark::class);
    }
}
