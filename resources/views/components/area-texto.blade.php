@props(['nombre', 'etiqueta', 'valor' => null, 'maximo' => 500])

<div>
    <label for="{{ $nombre }}" class="etiqueta">{{ $etiqueta }}</label>
    <textarea id="{{ $nombre }}" name="{{ $nombre }}" rows="3" maxlength="{{ $maximo }}" required class="campo"
              @error($nombre) aria-invalid="true" @enderror>{{ old($nombre, $valor) }}</textarea>
    @error($nombre)
        <p class="error-campo">{{ $message }}</p>
    @enderror
</div>

