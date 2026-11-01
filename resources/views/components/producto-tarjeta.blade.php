@props(['producto', 'calificacion' => null])

<article class="tarjeta flex flex-col overflow-hidden !p-0 transition motion-reduce:transition-none hover:shadow-md">
    <a href="{{ route('productos.show', $producto->getKey()) }}" tabindex="-1" aria-hidden="true">
        <img src="{{ \App\Support\ImagenProducto::url($producto) }}" alt="" width="320" height="240"
             loading="lazy" decoding="async" class="aspect-[4/3] w-full bg-emerald-50 object-cover">
    </a>

    <div class="flex flex-1 flex-col gap-2 p-4">
        <h2 class="text-base font-semibold leading-snug text-stone-900">
            <a href="{{ route('productos.show', $producto->getKey()) }}"
               class="hover:underline focus-visible:outline-2 focus-visible:outline-emerald-700">{{ $producto->nombre }}</a>
        </h2>

        <p class="text-xs text-stone-600">
            {{ $producto->productor?->nombre_finca_asociacion }} · {{ $producto->productor?->municipio }}
        </p>

        <div class="flex flex-wrap items-center gap-2">
            @if ($producto->productor?->estaVerificado())
                <x-insignia-verificado />
            @endif
            <x-calificacion :datos="$calificacion" />
        </div>

        <div class="mt-auto pt-2">
            <div class="flex items-end justify-between gap-2 mb-3">
                <p class="text-lg font-bold text-emerald-800">
                    @cop($producto->precio)
                    <span class="text-xs font-normal text-stone-600">/ {{ $producto->unidad_medida }}</span>
                </p>
                <x-estado-stock :producto="$producto" />
            </div>

            @if ($producto->estaAgotado())
                <button type="button" disabled class="btn-secundario !min-h-9 w-full text-xs opacity-50 cursor-not-allowed">
                    Agotado
                </button>
            @else
                <form method="POST" action="{{ route('carrito.agregar') }}">
                    @csrf
                    <input type="hidden" name="producto_id" value="{{ $producto->getKey() }}">
                    <input type="hidden" name="cantidad" value="1">
                    <button type="submit" class="btn !min-h-9 w-full text-xs">
                        🛒 Añadir al carrito
                    </button>
                </form>
            @endif
        </div>
    </div>
</article>
