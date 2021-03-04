<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    public $timestamps = false;

    protected $table = 'estudiante';

    public function estudiantesPensums()
    {
      return $this->hasMany(EstudiantePensum::class, 'pege_id', 'pege_id')->orderBy('unid_nombre')->orderBy('prog_nombre');
    }

    public function estudiantesEstados()
    {
      return $this->hasMany(EstudianteEstado::class, 'pege_id', 'pege_id');
    }

}
