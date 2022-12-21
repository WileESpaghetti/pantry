<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SocialIdentity extends Model
{
    protected $fillable = ['provider_name', 'provider_id', 'access_token', 'refresh_token'];

    protected $hidden = ['access_token', 'refresh_token'];

    public function user() {
        return $this->belongsTo('Pantry\User');
    }
}
