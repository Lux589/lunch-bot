<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //

    public function staff(){
        return $this->belongsTo('App\Staff');
    }

    public function opt(){
        return $this->belongsTo('App\Opt','opts_id');
    }
}
