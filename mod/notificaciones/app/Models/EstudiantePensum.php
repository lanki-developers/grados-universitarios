<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstudiantePensum extends Model
{
  public $timestamps = false;

  protected $table = 'estudiante_pensum';

  public function estudiante(){
    return $this->belongsTo(Estudiante::class,'pege_id','pege_id');
  }

  public function estudianteEstado(){
    return $this->hasOne(EstudianteEstado::class, 'estp_id', 'estp_id');
  }

    public function cat(){
    return $this->hasOne(CAT::class,'codigo_cat','unid_id');
  }

}
