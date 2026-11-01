@extends('layouts.app')

@section('titulo', 'Finalizar Pedido')

@section('contenido')
    <nav aria-label="Ruta de navegación" class="mb-4 text-sm">
        <a href="{{ route('carrito.index') }}" class="font-semibold text-emerald-800 underline underline-offset-2">← Volver al carrito</a>
    </nav>

    <h1 class="text-2xl font-bold text-stone-900">Finalizar Pedido</h1>
    <p class="text-stone-600 text-sm mt-0.5">Selecciona el tipo de entrega y tu método de pago preferido.</p>

    @if ($errors->any())
        <div class="alerta-error mt-4" role="alert">
            Por favor verifica los datos ingresados antes de confirmar.
        </div>
    @endif

    <form method="POST" action="{{ route('carrito.procesar') }}" class="mt-6 grid gap-6 lg:grid-cols-3">
        @csrf

        <div class="lg:col-span-2 space-y-6">
            {{-- Paso 1: Tipo de Entrega --}}
            <div class="tarjeta">
                <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2">1. Tipo de Entrega</h2>
                
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <label class="flex items-start gap-3 rounded-xl border border-stone-200 p-3 hover:border-emerald-600 cursor-pointer">
                        <input type="radio" name="tipo_entrega" value="domicilio" @checked(old('tipo_entrega', 'domicilio') === 'domicilio')
                               class="mt-1 text-emerald-700 focus:ring-emerald-600" onchange="toggleEnvio(true)">
                        <div>
                            <span class="block font-bold text-stone-900 text-sm">Envío a Domicilio</span>
                            <span class="block text-xs text-stone-500">Recibe en tu casa u oficina ($ 4.000 COP)</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 rounded-xl border border-stone-200 p-3 hover:border-emerald-600 cursor-pointer">
                        <input type="radio" name="tipo_entrega" value="recogida_punto" @checked(old('tipo_entrega') === 'recogida_punto')
                               class="mt-1 text-emerald-700 focus:ring-emerald-600" onchange="toggleEnvio(false)">
                        <div>
                            <span class="block font-bold text-stone-900 text-sm">Recogida en Punto / Finca</span>
                            <span class="block text-xs text-stone-500">Recoge directamente sin costo de flete ($ 0 COP)</span>
                        </div>
                    </label>
                </div>

                {{-- Dirección de entrega --}}
                <div class="mt-5 space-y-3 pt-4 border-t border-stone-100">
                    <h3 class="text-sm font-bold text-stone-800">Dirección de Entrega / Referencia</h3>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="municipio" class="etiqueta text-xs">Municipio (Santander)</label>
                            <select id="municipio" name="municipio" required class="campo !min-h-10 text-sm">
                                <option value="">Selecciona municipio</option>
                                @foreach ($municipios as $muni)
                                    <option value="{{ $muni }}" @selected(old('municipio', auth()->user()->direcciones[0]['municipio'] ?? '') === $muni)>{{ $muni }}</option>
                                @endforeach
                            </select>
                            @error('municipio') <p class="error-campo text-xs">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="direccion" class="etiqueta text-xs">Dirección de entrega</label>
                            <input id="direccion" name="direccion" type="text" value="{{ old('direccion', auth()->user()->direcciones[0]['direccion'] ?? '') }}"
                                   placeholder="Ej. Calle 45 # 20-30" required class="campo !min-h-10 text-sm">
                            @error('direccion') <p class="error-campo text-xs">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="barrio" class="etiqueta text-xs">Barrio o Vereda (opcional)</label>
                            <input id="barrio" name="barrio" type="text" value="{{ old('barrio', auth()->user()->direcciones[0]['barrio'] ?? '') }}"
                                   placeholder="Ej. Cabecera / Álvarez" class="campo !min-h-10 text-sm">
                        </div>

                        <div>
                            <label for="referencia" class="etiqueta text-xs">Indicaciones adicionales</label>
                            <input id="referencia" name="referencia" type="text" value="{{ old('referencia', auth()->user()->direcciones[0]['referencia'] ?? '') }}"
                                   placeholder="Ej. Conjunto Torres del Parque, Apto 302" class="campo !min-h-10 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paso 2: Método de Pago --}}
            <div class="tarjeta">
                <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2">2. Método de Pago</h2>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <label class="flex items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-emerald-600 cursor-pointer">
                        <input type="radio" name="metodo_pago" value="PSE" @checked(old('metodo_pago', 'PSE') === 'PSE') class="text-emerald-700">
                        <span class="font-semibold text-stone-900 text-sm">PSE (Débito en línea)</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-emerald-600 cursor-pointer">
                        <input type="radio" name="metodo_pago" value="tarjeta_credito" @checked(old('metodo_pago') === 'tarjeta_credito') class="text-emerald-700">
                        <span class="font-semibold text-stone-900 text-sm">Tarjeta de Crédito</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-emerald-600 cursor-pointer">
                        <input type="radio" name="metodo_pago" value="tarjeta_debito" @checked(old('metodo_pago') === 'tarjeta_debito') class="text-emerald-700">
                        <span class="font-semibold text-stone-900 text-sm">Tarjeta de Débito</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-emerald-600 cursor-pointer">
                        <input type="radio" name="metodo_pago" value="transferencia" @checked(old('metodo_pago') === 'transferencia') class="text-emerald-700">
                        <span class="font-semibold text-stone-900 text-sm">Transferencia Bancaria</span>
                    </label>

                    <label class="flex items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-emerald-600 cursor-pointer sm:col-span-2">
                        <input type="radio" name="metodo_pago" value="efectivo" @checked(old('metodo_pago') === 'efectivo') class="text-emerald-700">
                        <div>
                            <span class="font-semibold text-stone-900 text-sm">Efectivo / Pago Contra Entrega</span>
                            <span class="block text-xs text-stone-500">Pagas en efectivo al momento de recibir tus productos</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Resumen y Confirmación --}}
        <div>
            <div class="tarjeta sticky top-20">
                <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2">Resumen del Pedido</h2>

                <ul class="divide-y divide-stone-100 my-3 text-xs max-h-48 overflow-y-auto">
                    @foreach ($detalle as $linea)
                        <li class="py-2 flex justify-between gap-2">
                            <div>
                                <span class="font-semibold text-stone-800">{{ $linea['producto']->nombre }}</span>
                                <span class="text-stone-500 block">x {{ $linea['cantidad'] }} {{ $linea['producto']->unidad_medida }}</span>
                            </div>
                            <span class="font-semibold text-stone-900">@cop($linea['subtotal'])</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="border-t border-stone-100 pt-3 space-y-1.5 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-stone-600">Subtotal productos</dt>
                        <dd class="font-semibold text-stone-900">@cop($subtotal)</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-stone-600">Costo de envío</dt>
                        <dd id="envio-resumen" class="font-semibold text-stone-900">$ 4.000</dd>
                    </div>
                    <div class="flex justify-between border-t border-stone-200 pt-2 text-base font-extrabold text-emerald-900">
                        <dt>Total a pagar</dt>
                        <dd id="total-resumen">@cop(bcadd($subtotal, '4000', 2))</dd>
                    </div>
                </dl>

                <button type="submit" class="btn w-full mt-6 !min-h-11 font-bold text-base">
                    ✓ Confirmar y Crear Pedido
                </button>
            </div>
        </div>
    </form>

    <script>
        function toggleEnvio(esDomicilio) {
            const subtotal = parseFloat('{{ $subtotal }}');
            const envio = esDomicilio ? 4000 : 0;
            const total = subtotal + envio;

            document.getElementById('envio-resumen').innerText = esDomicilio ? '$ 4.000' : '$ 0';
            document.getElementById('total-resumen').innerText = '$ ' + total.toLocaleString('es-CO');
        }
    </script>
@endsection

