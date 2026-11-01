@extends('layouts.app')

@section('titulo', 'Detalle del Pedido #' . substr((string) $pedido->getKey(), -6))

@section('contenido')
    <nav aria-label="Ruta de navegación" class="mb-4 text-sm">
        <a href="{{ route('pedidos.index') }}" class="font-semibold text-emerald-800 underline underline-offset-2">← Volver a mis pedidos</a>
    </nav>

    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 pb-4">
        <div>
            <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Pedido #{{ (string) $pedido->getKey() }}</span>
            <h1 class="text-2xl font-bold text-stone-900 mt-0.5">Detalle del Pedido</h1>
            <p class="text-xs text-stone-500">Realizado el {{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y \a \l\a\s h:i A') }}</p>
        </div>

        <div>
            @php
                $colores = [
                    'pendiente' => 'bg-amber-100 text-amber-900 border-amber-300',
                    'confirmado' => 'bg-blue-100 text-blue-900 border-blue-300',
                    'en_camino' => 'bg-purple-100 text-purple-900 border-purple-300',
                    'entregado' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                    'cancelado' => 'bg-stone-100 text-stone-800 border-stone-300',
                ];
            @endphp
            <span class="inline-flex rounded-xl border px-3 py-1.5 text-sm font-bold capitalize {{ $colores[$pedido->estado->value] ?? 'bg-stone-100 text-stone-800' }}">
                Estado: {{ str_replace('_', ' ', $pedido->estado->value) }}
            </span>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- Ítems y Calificación --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="tarjeta">
                <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2">Productos del Pedido</h2>

                <div class="divide-y divide-stone-100 mt-2">
                    @foreach ($pedido->items as $item)
                        <div class="py-4 space-y-2">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-bold text-stone-900">{{ $item['nombre_producto'] }}</h3>
                                    <p class="text-xs text-stone-500">
                                        Cantidad: <span class="font-semibold">{{ $item['cantidad'] }}</span> | 
                                        Precio unitario: <span class="font-semibold">@cop($item['precio_unitario'])</span>
                                    </p>
                                </div>
                                <span class="font-extrabold text-emerald-900 text-base">@cop($item['subtotal'])</span>
                            </div>

                            {{-- Calificación del producto (si está entregado) --}}
                            @if ($pedido->estado->value === 'entregado')
                                <div class="bg-stone-50 p-3 rounded-xl border border-stone-200/80 mt-2">
                                    @if (isset($item['calificacion_producto']))
                                        <p class="text-xs text-stone-700 flex items-center gap-1.5">
                                            <span class="font-semibold">Tu opinión:</span>
                                            <span class="text-amber-600 font-bold">★ {{ $item['calificacion_producto'] }}/5</span>
                                            @if (!empty($item['comentario_producto']))
                                                <span class="italic text-stone-600">"{{ $item['comentario_producto'] }}"</span>
                                            @endif
                                        </p>
                                    @else
                                        <form method="POST" action="{{ route('pedidos.calificar-item', $pedido->getKey()) }}" class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="producto_id" value="{{ (string) $item['producto_id'] }}">
                                            <span class="text-xs font-bold text-stone-700">Calificar:</span>
                                            <select name="calificacion" class="campo !min-h-8 !w-auto text-xs py-1" required>
                                                <option value="5">⭐⭐⭐⭐⭐ (5 - Excelente)</option>
                                                <option value="4">⭐⭐⭐⭐ (4 - Muy bueno)</option>
                                                <option value="3">⭐⭐⭐ (3 - Bueno)</option>
                                                <option value="2">⭐⭐ (2 - Regular)</option>
                                                <option value="1">⭐ (1 - Malo)</option>
                                            </select>
                                            <input type="text" name="comentario" placeholder="Comentario opcional..." class="campo !min-h-8 flex-1 text-xs py-1">
                                            <button type="submit" class="btn !min-h-8 !px-3 !py-1 text-xs">Enviar</button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Información de la Entrega y Calificación de la Entrega --}}
            <div class="tarjeta">
                <div class="flex items-center justify-between border-b border-stone-100 pb-2">
                    <h2 class="text-base font-bold text-stone-900">Seguimiento de Entrega</h2>
                    <span class="insignia capitalize">{{ $entrega ? str_replace('_', ' ', $entrega->estado_entrega->value) : 'En preparación' }}</span>
                </div>

                <dl class="grid gap-3 sm:grid-cols-2 text-xs mt-4">
                    <div>
                        <dt class="text-stone-500 font-medium">Modalidad</dt>
                        <dd class="font-bold text-stone-900 text-sm capitalize">{{ str_replace('_', ' ', $pedido->tipo_entrega->value) }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500 font-medium">Fecha Programada</dt>
                        <dd class="font-semibold text-stone-800">
                            {{ $entrega && $entrega->fecha_programada ? \Carbon\Carbon::parse($entrega->fecha_programada)->format('d/m/Y') : 'Por definir' }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-stone-500 font-medium">Dirección / Punto</dt>
                        <dd class="font-semibold text-stone-900">
                            {{ $pedido->direccion_entrega['direccion'] ?? '' }} ({{ $pedido->direccion_entrega['municipio'] ?? '' }})
                            @if (!empty($pedido->direccion_entrega['barrio'])) - Barrio {{ $pedido->direccion_entrega['barrio'] }} @endif
                            @if (!empty($pedido->direccion_entrega['referencia'])) · Ref: {{ $pedido->direccion_entrega['referencia'] }} @endif
                        </dd>
                    </div>
                </dl>

                {{-- Calificación de la entrega (si ya fue entregada) --}}
                @if ($entrega && $entrega->estado_entrega->value === 'entregado')
                    <div class="mt-4 pt-4 border-t border-stone-100 bg-stone-50 p-3 rounded-xl border border-stone-200/80">
                        @if ($entrega->calificacion_entrega)
                            <p class="text-xs text-stone-700 flex items-center gap-1.5">
                                <span class="font-semibold">Calificación del servicio logístico:</span>
                                <span class="text-amber-600 font-bold">★ {{ $entrega->calificacion_entrega }}/5</span>
                                @if (!empty($entrega->comentario_entrega))
                                    <span class="italic text-stone-600">"{{ $entrega->comentario_entrega }}"</span>
                                @endif
                            </p>
                        @else
                            <form method="POST" action="{{ route('entregas.calificar', $entrega->getKey()) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <span class="text-xs font-bold text-stone-700">Calificar entrega:</span>
                                <select name="calificacion" class="campo !min-h-8 !w-auto text-xs py-1" required>
                                    <option value="5">⭐⭐⭐⭐⭐ (5 - Puntual y perfecto)</option>
                                    <option value="4">⭐⭐⭐⭐ (4 - Muy bueno)</option>
                                    <option value="3">⭐⭐⭐ (3 - Regular)</option>
                                    <option value="2">⭐⭐ (2 - Con demoras)</option>
                                    <option value="1">⭐ (1 - Mal servicio)</option>
                                </select>
                                <input type="text" name="comentario" placeholder="Comentario sobre la entrega..." class="campo !min-h-8 flex-1 text-xs py-1">
                                <button type="submit" class="btn !min-h-8 !px-3 !py-1 text-xs">Calificar Entrega</button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Resumen Económico y Pagos --}}
        <div class="space-y-6">
            <div class="tarjeta">
                <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2">Totales</h2>

                <dl class="space-y-2 text-sm mt-3">
                    <div class="flex justify-between">
                        <dt class="text-stone-600">Subtotal</dt>
                        <dd class="font-bold text-stone-900">@cop($pedido->subtotal)</dd>
                    </div>
                    <div class="flex justify-between text-xs">
                        <dt class="text-stone-500">Envío</dt>
                        <dd class="font-semibold text-stone-800">@cop($pedido->costo_envio)</dd>
                    </div>
                    <div class="flex justify-between border-t border-stone-200 pt-2 text-lg font-extrabold text-emerald-950">
                        <dt>Total</dt>
                        <dd>@cop($pedido->total)</dd>
                    </div>
                </dl>
            </div>

            {{-- Historial de Pagos --}}
            <div class="tarjeta">
                <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2">Comprobantes de Pago</h2>

                <div class="divide-y divide-stone-100 mt-3 text-xs">
                    @forelse ($pagos as $pago)
                        <div class="py-2.5 space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-stone-800 uppercase">{{ str_replace('_', ' ', $pago->metodo_pago->value) }}</span>
                                @php
                                    $coloresPago = [
                                        'pendiente' => 'bg-amber-100 text-amber-900',
                                        'aprobado' => 'bg-emerald-100 text-emerald-900',
                                        'rechazado' => 'bg-red-100 text-red-900',
                                        'reembolsado' => 'bg-stone-200 text-stone-800',
                                    ];
                                @endphp
                                <span class="rounded-full px-2 py-0.2 font-semibold capitalize {{ $coloresPago[$pago->estado->value] ?? 'bg-stone-100' }}">
                                    {{ $pago->estado->value }}
                                </span>
                            </div>
                            <div class="flex justify-between text-stone-500">
                                <span>Ref: {{ $pago->referencia_transaccion }}</span>
                                <span class="font-semibold text-stone-800">@cop($pago->valor)</span>
                            </div>
                            <p class="text-[10px] text-stone-400">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y h:i A') }}</p>
                        </div>
                    @empty
                        <p class="text-stone-500 text-xs py-2">No hay pagos registrados para este pedido.</p>
                    @endforelse
                </div>

                {{-- Registrar nuevo intento de pago si el pedido no está cancelado o entregado --}}
                @if ($pedido->estado->value === 'pendiente')
                    <div class="mt-4 pt-3 border-t border-stone-100">
                        <h3 class="text-xs font-bold text-stone-800 mb-2">Registrar o reintentar pago:</h3>
                        <form method="POST" action="{{ route('pedidos.pago', $pedido->getKey()) }}" class="space-y-2">
                            @csrf
                            <select name="metodo_pago" class="campo !min-h-9 text-xs" required>
                                <option value="PSE">PSE en línea</option>
                                <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                <option value="tarjeta_debito">Tarjeta de Débito</option>
                                <option value="transferencia">Transferencia Bancaria</option>
                                <option value="efectivo">Efectivo contra entrega</option>
                            </select>
                            <button type="submit" class="btn !min-h-9 w-full text-xs">
                                💳 Registrar Pago
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

