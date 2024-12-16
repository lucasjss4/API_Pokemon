<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ability extends Model
{
    public function Pokemon(){
        return $this->belongsTo(Pokemon::class);
    }
}
