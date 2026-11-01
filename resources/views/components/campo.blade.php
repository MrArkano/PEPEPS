@props([
    'nombre',
    'etiqueta',
    'tipo' => 'text',
    'valor' => null,
    'ayuda' => null,
    'modo' => null,
    'maximo' => null,
    'minimo' => null,
    'paso' => null,
])

<div>
    <label for="{{ $nombre }}" class="etiqueta">{{ $etiqueta }}</label>
    <input id="{{ $nombre }}" name="{{ $nombre }}" type="{{ $tipo }}" value="{{ old($nombre, $valor) }}" required
           @if ($modo) inputmode="{{ $modo }}" @endif
           @if ($maximo !== null) @if ($tipo === 'number') max="{{ $maximo }}" @else maxlength="{{ $maximo }}" @endif @endif
           @if ($minimo !== null) min="{{ $minimo }}" @endif
           @if ($paso !== null) step="{{ $paso }}" @endif
           @if ($ayuda) aria-describedby="{{ $nombre }}-ayuda @error($nombre) {{ $nombre }}-error @enderror" @endif
           class="campo" @error($nombre) aria-invalid="true" @enderror>
    @if ($ayuda)
        <p id="{{ $nombre }}-ayuda" class="mt-1 text-xs text-stone-600">{{ $ayuda }}</p>
    @endif
    @error($nombre)
        <p id="{{ $nombre }}-error" class="error-campo">{{ $message }}</p>
    @enderror
</div>

