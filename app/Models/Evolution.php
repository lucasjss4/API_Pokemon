<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evolution extends Model
{
    public function pokemon(){
        return $this->belongsTo(Pokemon::class);
    }
}
