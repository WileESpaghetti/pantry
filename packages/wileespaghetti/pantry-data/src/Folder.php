<?php
declare(strict_types=1);

namespace Pantry;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pantry\Traits\HasUser;

class Folder extends Model
{
    use HasFactory, HasUser;

    /**
     * `color` and `links` can be filled only because they are needed for the Larder API
     */
    protected $fillable = ['name', 'color', 'links'];
}
