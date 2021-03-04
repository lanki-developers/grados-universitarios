<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstudianteEstado extends Model
{
  public $timestamps = false;

  protected $table = 'estudiante_estado';

  public function estudiante(){
    return $this->belongsTo(Estudiante::class,'pege_id', 'pege_id');
  }

  public function estudiantePensum(){
    return $this->hasOne(EstudiantePensum::class, 'estp_id', 'estp_id');
  }

  // public function estudiantePensumSeleccionado(){
  //   return $this->hasOne(EstudiantePensum::class, 'estp_id', 'estp_id')->where('seleccionado','Si')->where('estado',1);
  // }

}
