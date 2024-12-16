<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    public function pokemon(){
        return $this->belongsTo(Pokemon::class);
    }
}
