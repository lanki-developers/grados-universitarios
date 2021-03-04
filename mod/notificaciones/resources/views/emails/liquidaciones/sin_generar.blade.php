@component('mail::message')
Cordial saludo Srs Registro Académico, Direcciones Regionales y Coordinaciones de CAT

<div class="d-flex-mb-3">
    <div class="card mb-3">
        <div class="card-header">
            <h2 class="card-title">Liquidaciones Sin Generar</h2>
        </div>
        <div class="card-body">
            <p class="card-text text-justify">
              Se les informa por medio del presente que el siguiente listado de estudiantes cumplen con las validaciones de plan de estudio, liquidaciones sin pendientes y actualización de datos, pero aún no se ha generado la liquidación para Grados en el Sistema Academusoft.
            </p>
            {!! $listado !!}
            <p class="card-text">
              <a href="https://uniclaretiana.edu.co/academusoft/index.html" target="_blank">Ir a Academusoft</a>
            </p>
        </div>
    </div>
</div>

{{ config('app.name') }} Registro y Control
@endcomponent
