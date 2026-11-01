@props(['nombre', 'etiqueta', 'opciones', 'valor' => null, 'vacio' => null, 'requerido' => true])

<div>
    <label for="{{ $nombre }}" class="etiqueta">{{ $etiqueta }}</label>
    <select id="{{ $nombre }}" name="{{ $nombre }}" @required($requerido) class="campo" @error($nombre) aria-invalid="true" @enderror>
        @if ($vacio !== null)
            <option value="">{{ $vacio }}</option>
        @endif
        @foreach ($opciones as $clave => $texto)
            <option value="{{ $clave }}" @selected((string) old($nombre, $valor) === (string) $clave)>{{ $texto }}</option>
        @endforeach
    </select>
    @error($nombre)
        <p class="error-campo">{{ $message }}</p>
    @enderror
</div>

