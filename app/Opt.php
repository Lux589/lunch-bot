<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Opt extends Model
{
    //

    public function orders(){
        return $this->hasMany('App\Order','opts_id','id');
    }
}
