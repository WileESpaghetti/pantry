<?php
declare(strict_types=1);

namespace App;

use App\Traits\HasUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory, HasUser;

    /**
     * `color` and `links` can be filled only because they are needed for the Larder API
     */
    protected $fillable = ['name', 'color', 'links'];
}
