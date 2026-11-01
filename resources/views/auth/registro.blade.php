@extends('layouts.app')

@section('titulo', 'Crear cuenta')

@section('contenido')
    <section class="mx-auto max-w-md" aria-labelledby="titulo-registro">
        <div class="tarjeta">
            <h1 id="titulo-registro" class="text-2xl font-bold text-stone-900">Crear cuenta</h1>
            <p class="mt-1 text-sm text-stone-600">Regístrate como consumidor para comprar productos campesinos.</p>

            <form method="POST" action="{{ route('registro.almacenar') }}" class="mt-5 space-y-4" novalidate>
                @csrf

                @php
                    $campos = [
                        ['nombre', 'Nombre', 'text', 'given-name'],
                        ['apellido', 'Apellido', 'text', 'family-name'],
                        ['correo', 'Correo electrónico', 'email', 'email'],
                        ['telefono', 'Celular (10 dígitos)', 'tel', 'tel-national'],
                    ];
                @endphp

                @foreach ($campos as [$nombre, $etiqueta, $tipo, $auto])
                    <div>
                        <label for="{{ $nombre }}" class="etiqueta">{{ $etiqueta }}</label>
                        <input id="{{ $nombre }}" name="{{ $nombre }}" type="{{ $tipo }}" value="{{ old($nombre) }}" required
                               autocomplete="{{ $auto }}" @if ($tipo === 'tel') inputmode="numeric" @endif
                               class="campo" @error($nombre) aria-invalid="true" aria-describedby="{{ $nombre }}-error" @enderror>
                        @error($nombre)
                            <p id="{{ $nombre }}-error" class="error-campo">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                <div>
                    <label for="contrasena" class="etiqueta">Contraseña</label>
                    <input id="contrasena" name="contrasena" type="password" required minlength="8"
                           autocomplete="new-password" aria-describedby="ayuda-clave"
                           class="campo" @error('contrasena') aria-invalid="true" @enderror>
                    <p id="ayuda-clave" class="mt-1 text-xs text-stone-600">Mínimo 8 caracteres, con letras y números.</p>
                    @error('contrasena')
                        <p class="error-campo">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="contrasena_confirmation" class="etiqueta">Repite la contraseña</label>
                    <input id="contrasena_confirmation" name="contrasena_confirmation" type="password" required
                           autocomplete="new-password" class="campo">
                </div>

                <button type="submit" class="btn w-full">Crear cuenta</button>
            </form>
        </div>

        <p class="mt-4 text-center text-sm">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="font-semibold text-emerald-800 underline underline-offset-2">Ingresar</a>
        </p>
    </section>
@endsection

