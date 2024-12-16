<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model
{
    public function egg(){
        return $this->hasMany(Egg::class);
    }

    public function ability(){
        return $this->hasMany(Ability::class);
    }

    public function evolution(){
        return $this->hasOne(Evolution::class);
    }

    public function type(){
        return $this->hasMany(Type::class);
    }
}
