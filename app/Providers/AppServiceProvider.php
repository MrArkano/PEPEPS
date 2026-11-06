<?php

namespace App\Providers;

use App\Repositories\Contracts\CalificacionRepositorio;
use App\Repositories\Contracts\CategoriaRepositorio;
use App\Repositories\Contracts\EntregaRepositorio;
use App\Repositories\Contracts\PagoRepositorio;
use App\Repositories\Contracts\PedidoRepositorio;
use App\Repositories\Contracts\ProductoRepositorio;
use App\Repositories\Contracts\ProductorRepositorio;
use App\Repositories\Contracts\ReporteRepositorio;
use App\Repositories\Contracts\UsuarioRepositorio;
use App\Repositories\Mongo\CalificacionRepositorioMongo;
use App\Repositories\Mongo\CategoriaRepositorioMongo;
use App\Repositories\Mongo\EntregaRepositorioMongo;
use App\Repositories\Mongo\PagoRepositorioMongo;
use App\Repositories\Mongo\PedidoRepositorioMongo;
use App\Repositories\Mongo\ProductoRepositorioMongo;
use App\Repositories\Mongo\ProductorRepositorioMongo;
use App\Repositories\Mongo\ReporteRepositorioMongo;
use App\Repositories\Mongo\UsuarioRepositorioMongo;
use App\Support\Moneda;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Contratos de persistencia => implementación MongoDB.
     * Migrar a otro motor solo exige nuevas implementaciones y cambiar este mapa.
     */
    private const REPOSITORIOS = [
        UsuarioRepositorio::class => UsuarioRepositorioMongo::class,
        ProductoRepositorio::class => ProductoRepositorioMongo::class,
        ProductorRepositorio::class => ProductorRepositorioMongo::class,
        CategoriaRepositorio::class => CategoriaRepositorioMongo::class,
        CalificacionRepositorio::class => CalificacionRepositorioMongo::class,
        PedidoRepositorio::class => PedidoRepositorioMongo::class,
        PagoRepositorio::class => PagoRepositorioMongo::class,
        EntregaRepositorio::class => EntregaRepositorioMongo::class,
        ReporteRepositorio::class => ReporteRepositorioMongo::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach (self::REPOSITORIOS as $contrato => $implementacion) {
            $this->app->bind($contrato, $implementacion);
        }

        $this->app->bind(StatefulGuard::class, static fn (): StatefulGuard => Auth::guard('web'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || str_contains((string) config('app.url'), 'https://') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

        // Los ids de ruta deben ser ObjectId hexadecimales; cualquier otro valor da 404.
        Route::pattern('id', '[a-fA-F0-9]{24}');
        Route::pattern('producto', '[a-fA-F0-9]{24}');
        Route::pattern('finca', '[a-fA-F0-9]{24}');
        Route::pattern('pedido', '[a-fA-F0-9]{24}');
        Route::pattern('pago', '[a-fA-F0-9]{24}');
        Route::pattern('entrega', '[a-fA-F0-9]{24}');

        Route::bind('producto', static fn (string $id) => app(ProductoRepositorio::class)->encontrarOFallar($id));
        Route::bind('finca', static fn (string $id) => app(ProductorRepositorio::class)->encontrarOFallar($id));
        Route::bind('pedido', static fn (string $id) => app(PedidoRepositorio::class)->encontrarOFallar($id));
        Route::bind('pago', static fn (string $id) => app(PagoRepositorio::class)->encontrarOFallar($id));
        Route::bind('entrega', static fn (string $id) => app(EntregaRepositorio::class)->encontrarOFallar($id));

        Blade::directive('cop', static fn (string $expresion): string => "<?php echo e(\\".Moneda::class."::formatear({$expresion})); ?>");
    }
}
