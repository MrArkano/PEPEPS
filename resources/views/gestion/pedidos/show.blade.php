@extends('layouts.app')

@php
    $esAdmin = auth()->user()->tieneRol(\App\Enums\Rol::Administrador);
@endphp

@section('titulo', 'Gestionar Pedido #' . substr((string) $pedido->getKey(), -6))

@section('contenido')
    <nav aria-label="Ruta de navegación" class="mb-4 text-sm">
        <a href="{{ route('gestion.pedidos.index') }}" class="font-semibold text-emerald-800 underline underline-offset-2">← Volver a listado de pedidos</a>
    </nav>

    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 pb-4">
        <div>
            <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Gestión de Pedido #{{ (string) $pedido->getKey() }}</span>
            <h1 class="text-2xl font-bold text-stone-900 mt-0.5">Control y Despacho</h1>
            <p class="text-xs text-stone-500">
                Cliente: <span class="font-bold text-stone-800">{{ $pedido->consumidor?->nombre_completo ?? 'Consumidor' }}</span> ({{ $pedido->consumidor?->correo ?? '—' }})
            </p>
        </div>

        {{-- Formulario para cambiar el estado del pedido --}}
        <form method="POST" action="{{ route('gestion.pedidos.estado', $pedido->getKey()) }}" class="flex items-center gap-2">
            @csrf
            @method('PATCH')
            <label for="estado" class="text-xs font-bold text-stone-700">Estado del Pedido:</label>
            <select id="estado" name="estado" class="campo !min-h-9 !w-auto text-xs font-semibold py-1 capitalize" onchange="this.form.submit()">
                @foreach (['pendiente' => 'Pendiente', 'confirmado' => 'Confirmado', 'en_camino' => 'En Camino', 'entregado' => 'Entregado', 'cancelado' => 'Cancelado'] as $k => $nom)
                    <option value="{{ $k }}" @selected($pedido->estado->value === $k)>{{ $nom }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            {{-- Detalle de productos comprados (foto histórica) --}}
            <div class="tarjeta">
                <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2">Ítems Solicitados</h2>
                <div class="divide-y divide-stone-100 mt-2">
                    @foreach ($pedido->items as $item)
                        <div class="py-3 flex items-center justify-between gap-3 text-sm">
                            <div>
                                <h3 class="font-bold text-stone-900">{{ $item['nombre_producto'] }}</h3>
                                <p class="text-xs text-stone-500">
                                    Cantidad: <span class="font-semibold">{{ $item['cantidad'] }}</span> × 
                                    @cop($item['precio_unitario'])
                                </p>
                                @if (isset($item['calificacion_producto']))
                                    <p class="text-xs text-amber-800 mt-1">
                                        ⭐ Calificación del cliente: {{ $item['calificacion_producto'] }}/5 
                                        @if (!empty($item['comentario_producto']))
                                            <span class="italic text-stone-600 font-normal">"{{ $item['comentario_producto'] }}"</span>
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <span class="font-bold text-emerald-900">@cop($item['subtotal'])</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Logística de Entrega --}}
            <div class="tarjeta">
                <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2">Logística de Entrega</h2>

                @if ($entrega)
                    <form method="POST" action="{{ route('gestion.entregas.actualizar', $entrega->getKey()) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="etiqueta text-xs">Estado de la Entrega</label>
                                <select name="estado_entrega" class="campo !min-h-9 text-xs" required>
                                    @foreach (['programada' => 'Programada', 'en_camino' => 'En Camino', 'entregado' => 'Entregado', 'cancelada' => 'Cancelada'] as $k => $nom)
                                        <option value="{{ $k }}" @selected($entrega->estado_entrega->value === $k)>{{ $nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="etiqueta text-xs">Tipo de Responsable</label>
                                <select name="tipo_responsable" class="campo !min-h-9 text-xs">
                                    <option value="Productor" @selected($entrega->tipo_responsable->value === 'Productor')>Productor Rural</option>
                                    <option value="Transportador" @selected($entrega->tipo_responsable->value === 'Transportador')>Transportador / Operador</option>
                                </select>
                            </div>

                            <div>
                                <label class="etiqueta text-xs">Fecha Programada</label>
                                <input type="date" name="fecha_programada"
                                       value="{{ $entrega->fecha_programada ? \Carbon\Carbon::parse($entrega->fecha_programada)->format('Y-m-d') : '' }}"
                                       class="campo !min-h-9 text-xs">
                            </div>

                            <div>
                                <label class="etiqueta text-xs">Fecha Efectiva de Entrega</label>
                                <input type="date" name="fecha_entrega"
                                       value="{{ $entrega->fecha_entrega ? \Carbon\Carbon::parse($entrega->fecha_entrega)->format('Y-m-d') : '' }}"
                                       class="campo !min-h-9 text-xs">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="etiqueta text-xs">Observaciones Logísticas</label>
                                <input type="text" name="observaciones" value="{{ $entrega->observaciones }}" class="campo !min-h-9 text-xs" placeholder="Novedades de la ruta, operador asignado...">
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="btn !min-h-9 text-xs">
                                Guardar Logística
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-xs text-stone-500 py-3">No hay registro de entrega para este pedido.</p>
                @endif
            </div>
        </div>

        {{-- Información de Pago y Conciliación --}}
        <div class="space-y-6">
            <div class="tarjeta">
                <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2">Destino y Contacto</h2>
                <dl class="text-xs space-y-2 mt-3">
                    <div>
                        <dt class="text-stone-500">Tipo de Entrega</dt>
                        <dd class="font-bold text-stone-800 capitalize">{{ str_replace('_', ' ', $pedido->tipo_entrega->value) }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Municipio</dt>
                        <dd class="font-bold text-stone-800">{{ $pedido->direccion_entrega['municipio'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Dirección</dt>
                        <dd class="font-semibold text-stone-800">{{ $pedido->direccion_entrega['direccion'] ?? '—' }}</dd>
                    </div>
                    @if (!empty($pedido->direccion_entrega['referencia']))
                        <div>
                            <dt class="text-stone-500">Referencia</dt>
                            <dd class="text-stone-700 italic">{{ $pedido->direccion_entrega['referencia'] }}</dd>
                        </div>
                    @endif
                    <div class="border-t border-stone-100 pt-2">
                        <dt class="text-stone-500">Total Liquidado</dt>
                        <dd class="text-base font-extrabold text-emerald-950">@cop($pedido->total)</dd>
                    </div>
                </dl>
            </div>

            {{-- Conciliación de Pagos --}}
            <div class="tarjeta">
                <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2">Pagos y Conciliación</h2>

                <div class="divide-y divide-stone-100 mt-3 text-xs">
                    @forelse ($pagos as $pago)
                        <div class="py-3 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-stone-800">{{ $pago->metodo_pago->value }}</span>
                                <span class="rounded px-2 py-0.5 font-bold uppercase {{ $pago->estado->value === 'aprobado' ? 'bg-emerald-100 text-emerald-900' : ($pago->estado->value === 'rechazado' ? 'bg-red-100 text-red-900' : 'bg-amber-100 text-amber-900') }}">
                                    {{ $pago->estado->value }}
                                </span>
                            </div>
                            <p class="text-stone-500">Ref: <span class="font-mono text-stone-700">{{ $pago->referencia_transaccion }}</span></p>
                            <p class="font-bold text-emerald-900">@cop($pago->valor)</p>

                            {{-- Solo el Administrador puede conciliar pagos --}}
                            @if ($esAdmin)
                                <form method="POST" action="{{ route('gestion.pagos.conciliar', $pago->getKey()) }}" class="flex items-center gap-2 pt-1">
                                    @csrf
                                    @method('PATCH')
                                    <select name="estado" class="campo !min-h-8 text-xs py-0.5 font-semibold">
                                        @foreach (['pendiente' => 'Pendiente', 'aprobado' => 'Aprobar', 'rechazado' => 'Rechazar', 'reembolsado' => 'Reembolsar'] as $k => $nom)
                                            <option value="{{ $k }}" @selected($pago->estado->value === $k)>{{ $nom }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn-secundario !min-h-8 !px-2.5 !py-0.5 text-xs font-bold">
                                        Conciliar
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-stone-500 text-xs py-2">Sin comprobantes de pago.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

