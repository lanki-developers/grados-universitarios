@component('mail::message')
Cordial saludo {{ $estudiante['nombre'] }},

<div class="d-flex-mb-3">
    <div class="card mb-3">
        <div class="card-header">
            <h2 class="card-title">Liquidación Generada</h2>
        </div>
        <div class="card-body">
            <p class="card-text text-justify">Se le informa que se ha generado una liquidación para el proceso de grados del programa {{ $estudiante['programa']  }} y se encuentra disponible para pago en el sistema de información Academusoft.</p>
            <p class="card-text">Datos de la liquidación:<br></p>
            <ul>
                <li><strong>Id</strong> {{ $liquidacion['id'] }}</li>
                <li><strong>Referencia</strong> {{ $liquidacion['referencia'] }}</li>
                <li><strong>Tipo</strong> {{ $liquidacion['tipo'] }}</li>
                <li><strong>Concepto</strong> {{ $liquidacion['concepto'] }}</li>
            </ul>
            <p class="card-text">
                <a
                href="https://uniclaretiana.edu.co/academusoft/index.html"
                target="_blank">
                Ir a Academusoft
                </a>
            </p>
        </div>
    </div>
</div>

{{ config('app.name') }} Registro y Control
@endcomponent
