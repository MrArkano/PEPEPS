<!DOCTYPE html>
<html lang="es" class="h-full bg-stone-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>@yield('titulo', 'Inicio') · PEPEPS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col font-sans text-stone-800 antialiased">
    <a href="#contenido"
       class="sr-only rounded-lg bg-white px-4 py-2 font-semibold text-emerald-800 focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-50">
        Saltar al contenido
    </a>

    @php
        $totalCarrito = app(\App\Services\Pedidos\CarritoService::class)->totalItems();
    @endphp

    <header class="border-b border-stone-200 bg-white sticky top-0 z-30 shadow-xs">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-3">
            <div class="flex items-center gap-6">
                <a href="{{ route('catalogo.index') }}" class="text-lg font-extrabold tracking-tight text-emerald-800 flex items-center gap-1.5">
                    <span>🌾 PEPEPS</span>
                    <span class="hidden text-xs font-medium text-emerald-950 sm:inline bg-emerald-100 rounded-md px-1.5 py-0.5">Santander</span>
                </a>

                <nav class="hidden md:flex items-center gap-4 text-sm font-semibold">
                    <a href="{{ route('catalogo.index') }}" class="hover:text-emerald-800 {{ request()->routeIs('catalogo.*', 'productos.*') ? 'text-emerald-800 underline underline-offset-4' : 'text-stone-600' }}">Catálogo</a>
                    @auth
                        <a href="{{ route('panel') }}" class="hover:text-emerald-800 {{ request()->routeIs('panel', '*.panel') ? 'text-emerald-800 underline underline-offset-4' : 'text-stone-600' }}">Mi Panel</a>
                        
                        @if (auth()->user()->tieneRol(\App\Enums\Rol::Consumidor))
                            <a href="{{ route('pedidos.index') }}" class="hover:text-emerald-800 {{ request()->routeIs('pedidos.*') ? 'text-emerald-800 underline underline-offset-4' : 'text-stone-600' }}">Mis Pedidos</a>
                        @endif

                        @if (auth()->user()->tieneRol(\App\Enums\Rol::Productor, \App\Enums\Rol::Administrador))
                            <a href="{{ route('gestion.productos.index') }}" class="hover:text-emerald-800 {{ request()->routeIs('gestion.productos.*') ? 'text-emerald-800 underline underline-offset-4' : 'text-stone-600' }}">Productos</a>
                            <a href="{{ route('gestion.inventario.index') }}" class="hover:text-emerald-800 {{ request()->routeIs('gestion.inventario.*') ? 'text-emerald-800 underline underline-offset-4' : 'text-stone-600' }}">Inventario</a>
                            <a href="{{ route('gestion.pedidos.index') }}" class="hover:text-emerald-800 {{ request()->routeIs('gestion.pedidos.*') ? 'text-emerald-800 underline underline-offset-4' : 'text-stone-600' }}">Pedidos</a>
                            <a href="{{ route('reportes.index') }}" class="hover:text-emerald-800 {{ request()->routeIs('reportes.*') ? 'text-emerald-800 underline underline-offset-4' : 'text-stone-600' }}">Reportes</a>
                            <a href="{{ route('gestion.fincas.index') }}" class="hover:text-emerald-800 {{ request()->routeIs('gestion.fincas.*') ? 'text-emerald-800 underline underline-offset-4' : 'text-stone-600' }}">Fincas</a>
                            @if (auth()->user()->tieneRol(\App\Enums\Rol::Administrador))
                                <a href="{{ route('gestion.auditoria.index') }}" class="hover:text-emerald-800 {{ request()->routeIs('gestion.auditoria.*') ? 'text-emerald-800 underline underline-offset-4' : 'text-stone-600' }}">Auditoría</a>
                            @endif
                        @endif
                    @endauth
                </nav>
            </div>

            <div class="flex items-center gap-3">
                {{-- Carrito de compras --}}
                <a href="{{ route('carrito.index') }}" class="relative inline-flex items-center gap-1.5 rounded-xl border border-stone-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-stone-800 hover:bg-stone-50" aria-label="Ver carrito">
                    <span class="text-sm">🛒</span>
                    <span class="hidden sm:inline">Carrito</span>
                    @if ($totalCarrito > 0)
                        <span class="rounded-full bg-emerald-700 px-1.5 py-0.2 text-[10px] font-bold text-white">{{ $totalCarrito }}</span>
                    @endif
                </a>

                @auth
                    <p class="text-right text-xs leading-tight hidden sm:block">
                        <span class="block max-w-32 truncate font-semibold text-stone-900 sm:max-w-none">{{ auth()->user()->nombre_completo }}</span>
                        <span class="insignia mt-0.5">{{ auth()->user()->rol->value }}</span>
                    </p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-secundario !min-h-9 !px-3 !py-1 text-xs">Salir</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-secundario !min-h-9 !px-3 !py-1 text-xs">Ingresar</a>
                    <a href="{{ route('registro') }}" class="btn !min-h-9 !px-3 !py-1 text-xs">Registrarse</a>
                @endauth
            </div>
        </div>

        {{-- Barra de navegación móvil secundaria --}}
        @auth
            <div class="flex md:hidden border-t border-stone-100 bg-stone-50/80 px-4 py-2 gap-3 text-xs font-semibold overflow-x-auto">
                <a href="{{ route('catalogo.index') }}" class="{{ request()->routeIs('catalogo.*', 'productos.*') ? 'text-emerald-800' : 'text-stone-600' }}">Catálogo</a>
                <a href="{{ route('panel') }}" class="{{ request()->routeIs('panel', '*.panel') ? 'text-emerald-800' : 'text-stone-600' }}">Panel</a>
                @if (auth()->user()->tieneRol(\App\Enums\Rol::Consumidor))
                    <a href="{{ route('pedidos.index') }}" class="{{ request()->routeIs('pedidos.*') ? 'text-emerald-800' : 'text-stone-600' }}">Mis Pedidos</a>
                @endif
                @if (auth()->user()->tieneRol(\App\Enums\Rol::Productor, \App\Enums\Rol::Administrador))
                    <a href="{{ route('gestion.productos.index') }}" class="{{ request()->routeIs('gestion.productos.*') ? 'text-emerald-800' : 'text-stone-600' }}">Productos</a>
                    <a href="{{ route('gestion.inventario.index') }}" class="{{ request()->routeIs('gestion.inventario.*') ? 'text-emerald-800' : 'text-stone-600' }}">Inventario</a>
                    <a href="{{ route('gestion.pedidos.index') }}" class="{{ request()->routeIs('gestion.pedidos.*') ? 'text-emerald-800' : 'text-stone-600' }}">Pedidos</a>
                    <a href="{{ route('reportes.index') }}" class="{{ request()->routeIs('reportes.*') ? 'text-emerald-800' : 'text-stone-600' }}">Reportes</a>
                    <a href="{{ route('gestion.fincas.index') }}" class="{{ request()->routeIs('gestion.fincas.*') ? 'text-emerald-800' : 'text-stone-600' }}">Fincas</a>
                    @if (auth()->user()->tieneRol(\App\Enums\Rol::Administrador))
                        <a href="{{ route('gestion.auditoria.index') }}" class="{{ request()->routeIs('gestion.auditoria.*') ? 'text-emerald-800' : 'text-stone-600' }}">Auditoría</a>
                    @endif
                @endif
            </div>
        @endauth
    </header>

    <main id="contenido" class="mx-auto w-full max-w-5xl flex-1 px-4 py-6 sm:py-10">
        @if (session('estado'))
            <div class="alerta-ok mb-4" role="status">{{ session('estado') }}</div>
        @endif

        @yield('contenido')
    </main>

    <footer class="border-t border-stone-200 py-6 text-center text-xs text-stone-600 bg-white">
        <p class="font-semibold text-stone-800">PEPEPS · Plataforma Digital de Mercados Campesinos de Santander</p>
        <p class="mt-1">Conectando la producción campesina rural con los hogares urbanos.</p>
    </footer>
</body>
</html>
