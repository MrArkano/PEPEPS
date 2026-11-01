@extends('layouts.app')

@section('titulo', 'Ingresar')

@section('contenido')
    <section class="mx-auto max-w-md" aria-labelledby="titulo-ingreso">
        <div class="tarjeta">
            <h1 id="titulo-ingreso" class="text-2xl font-bold text-stone-900">Ingresar</h1>
            <p class="mt-1 text-sm text-stone-600">Accede con tu correo y contraseña.</p>

            @if ($errors->any())
                <div class="alerta-error mt-4" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.almacenar') }}" class="mt-5 space-y-4" novalidate>
                @csrf

                <div>
                    <label for="correo" class="etiqueta">Correo electrónico</label>
                    <input id="correo" name="correo" type="email" value="{{ old('correo') }}" required
                           autocomplete="username" inputmode="email" autofocus
                           class="campo" @error('correo') aria-invalid="true" @enderror>
                </div>

                <div>
                    <label for="contrasena" class="etiqueta">Contraseña</label>
                    <input id="contrasena" name="contrasena" type="password" required
                           autocomplete="current-password" class="campo">
                </div>

                <button type="submit" class="btn w-full">Ingresar</button>
            </form>
        </div>

        <p class="mt-4 text-center text-sm">
            ¿No tienes cuenta?
            <a href="{{ route('registro') }}" class="font-semibold text-emerald-800 underline underline-offset-2">Regístrate</a>
        </p>
    </section>
@endsection

