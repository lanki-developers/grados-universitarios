<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CAT extends Model
{
  public $timestamps = false;

  protected $table = 'cat';

  public function estudinatePensum(){
    return $this->hasOne(EstudiantePensum::class,'unid_id','codigo_cat');
  }

}
