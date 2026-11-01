@extends('layouts.app')

@section('titulo', 'Panel del Administrador')

@section('contenido')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Panel de Administración</h1>
            <p class="mt-1 text-sm text-stone-600">Supervisión integral de la plataforma campesina PEPEPS.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('gestion.productos.create') }}" class="btn !min-h-10 text-sm">+ Nuevo Producto</a>
            <a href="{{ route('gestion.fincas.create') }}" class="btn-secundario !min-h-10 text-sm">+ Nueva Finca</a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('gestion.pedidos.index') }}" class="tarjeta flex flex-col justify-between hover:border-emerald-600 transition">
            <div>
                <span class="text-2xl">📑</span>
                <h2 class="text-base font-bold text-stone-900 mt-2">Órdenes y Despacho</h2>
                <p class="text-xs text-stone-600 mt-1">Supervisión de pedidos, asignación de transportadores y conciliación de pagos.</p>
            </div>
            <span class="text-xs font-semibold text-emerald-800 mt-3">Gestionar pedidos →</span>
        </a>

        <a href="{{ route('reportes.index') }}" class="tarjeta flex flex-col justify-between hover:border-emerald-600 transition">
            <div>
                <span class="text-2xl">📊</span>
                <h2 class="text-base font-bold text-stone-900 mt-2">Reportes NoSQL</h2>
                <p class="text-xs text-stone-600 mt-1">Las 5 consultas estratégicas sobre ventas, municipios, pagos e inventario crítico.</p>
            </div>
            <span class="text-xs font-semibold text-emerald-800 mt-3">Ver consultas y analítica →</span>
        </a>

        <a href="{{ route('gestion.productos.index') }}" class="tarjeta flex flex-col justify-between hover:border-emerald-600 transition">
            <div>
                <span class="text-2xl">📦</span>
                <h2 class="text-base font-bold text-stone-900 mt-2">Catálogo Completo</h2>
                <p class="text-xs text-stone-600 mt-1">Supervisa, edita o desactiva la oferta de todos los productores de Santander.</p>
            </div>
            <span class="text-xs font-semibold text-emerald-800 mt-3">Gestionar productos →</span>
        </a>

        <a href="{{ route('gestion.inventario.index') }}" class="tarjeta flex flex-col justify-between hover:border-emerald-600 transition">
            <div>
                <span class="text-2xl">📈</span>
                <h2 class="text-base font-bold text-stone-900 mt-2">Inventario Global</h2>
                <p class="text-xs text-stone-600 mt-1">Supervisa existencias, alertas de stock bajo y rotación de productos campesinos.</p>
            </div>
            <span class="text-xs font-semibold text-emerald-800 mt-3">Revisar inventarios →</span>
        </a>

        <a href="{{ route('gestion.fincas.index') }}" class="tarjeta flex flex-col justify-between hover:border-emerald-600 transition">
            <div>
                <span class="text-2xl">🏡</span>
                <h2 class="text-base font-bold text-stone-900 mt-2">Fincas y Verificación</h2>
                <p class="text-xs text-stone-600 mt-1">Verifica orígenes rurales y otorga la insignia oficial de Productor Verificado.</p>
            </div>
            <span class="text-xs font-semibold text-emerald-800 mt-3">Gestionar fincas →</span>
        </a>

        <a href="{{ route('gestion.auditoria.index') }}" class="tarjeta flex flex-col justify-between hover:border-emerald-600 transition">
            <div>
                <span class="text-2xl">🛡️</span>
                <h2 class="text-base font-bold text-stone-900 mt-2">Bitácora de Auditoría</h2>
                <p class="text-xs text-stone-600 mt-1">Trazabilidad de modificaciones de estados de pedidos, precios e inventario.</p>
            </div>
            <span class="text-xs font-semibold text-emerald-800 mt-3">Ver bitácora →</span>
        </a>
    </div>

    <div class="tarjeta mt-6">
        <h2 class="text-base font-bold text-stone-900">Estado de la Plataforma</h2>
        <div class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-4 text-center">
            <div class="rounded-xl bg-stone-50 p-3">
                <span class="text-2xl font-extrabold text-emerald-800">{{ \App\Models\Producto::count() }}</span>
                <span class="block text-xs text-stone-500 mt-0.5">Productos</span>
            </div>
            <div class="rounded-xl bg-stone-50 p-3">
                <span class="text-2xl font-extrabold text-emerald-800">{{ \App\Models\Productor::count() }}</span>
                <span class="block text-xs text-stone-500 mt-0.5">Fincas</span>
            </div>
            <div class="rounded-xl bg-stone-50 p-3">
                <span class="text-2xl font-extrabold text-emerald-800">{{ \App\Models\Pedido::count() }}</span>
                <span class="block text-xs text-stone-500 mt-0.5">Pedidos</span>
            </div>
            <div class="rounded-xl bg-stone-50 p-3">
                <span class="text-2xl font-extrabold text-amber-700">{{ \App\Models\Producto::all()->filter->esCritico()->count() }}</span>
                <span class="block text-xs text-stone-500 mt-0.5">Stock Crítico</span>
            </div>
        </div>
    </div>
@endsection
