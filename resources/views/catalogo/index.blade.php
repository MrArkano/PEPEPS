@extends('layouts.app')

@section('titulo', 'Catálogo')

@section('contenido')
    <h1 class="text-2xl font-bold text-stone-900">Catálogo campesino</h1>
    <p class="mt-1 text-stone-600">Productos frescos directo de las fincas de Santander.</p>

    <form method="GET" action="{{ route('catalogo.index') }}" class="tarjeta mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-5 lg:items-end"
          role="search" aria-label="Filtrar productos">
        <div class="lg:col-span-2">
            <label for="q" class="etiqueta">Buscar producto</label>
            <input id="q" name="q" type="search" value="{{ $filtros['q'] ?? '' }}" maxlength="60"
                   placeholder="Ej. mango, queso, café" class="campo">
        </div>

        <x-selector nombre="municipio" etiqueta="Municipio" :requerido="false" vacio="Todos"
                    :opciones="array_combine($pagina->municipios, $pagina->municipios)" :valor="$filtros['municipio'] ?? null" />

        <x-selector nombre="categoria" etiqueta="Categoría" :requerido="false" vacio="Todas"
                    :opciones="$pagina->categorias->mapWithKeys(fn ($c) => [(string) $c->getKey() => $c->nombre])->all()"
                    :valor="$filtros['categoria'] ?? null" />

        <x-selector nombre="productor" etiqueta="Finca o asociación" :requerido="false" vacio="Todas"
                    :opciones="$pagina->productores->mapWithKeys(fn ($p) => [(string) $p->getKey() => $p->nombre_finca_asociacion])->all()"
                    :valor="$filtros['productor'] ?? null" />

        <div class="flex gap-2 sm:col-span-2 lg:col-span-5">
            <button type="submit" class="btn">Buscar</button>
            <a href="{{ route('catalogo.index') }}" class="btn-secundario">Limpiar</a>
        </div>
    </form>

    <p class="mt-4 text-sm text-stone-600" role="status">
        {{ $pagina->productos->total() }} {{ $pagina->productos->total() === 1 ? 'producto encontrado' : 'productos encontrados' }}
    </p>

    @if ($pagina->productos->isEmpty())
        <div class="tarjeta mt-3 text-center text-stone-600">
            No encontramos productos con esos filtros. Prueba quitando alguno.
        </div>
    @else
        <ul class="mt-3 grid grid-cols-1 gap-4 min-[480px]:grid-cols-2 lg:grid-cols-3">
            @foreach ($pagina->productos as $producto)
                <li>
                    <x-producto-tarjeta :producto="$producto"
                        :calificacion="$pagina->calificacionesProductor[(string) $producto->productor_id] ?? null" />
                </li>
            @endforeach
        </ul>

        <div class="mt-6">{{ $pagina->productos->links() }}</div>
    @endif
@endsection

