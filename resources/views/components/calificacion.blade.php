@props(['datos' => null])

@if ($datos)
    <p class="flex items-center gap-1 text-sm text-stone-700"
       aria-label="Calificación {{ number_format($datos['promedio'], 1, ',', '') }} de 5 según {{ $datos['total'] }} opiniones">
        <span aria-hidden="true" class="text-amber-600">★</span>
        <span class="font-semibold">{{ number_format($datos['promedio'], 1, ',', '') }}</span>
        <span class="text-stone-600">({{ $datos['total'] }})</span>
    </p>
@else
    <p class="text-sm text-stone-600">Sin calificaciones aún</p>
@endif

