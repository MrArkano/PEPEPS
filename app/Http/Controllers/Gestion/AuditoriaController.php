<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Contracts\View\View;

final class AuditoriaController extends Controller
{
    public function __construct(private readonly AuditoriaService $auditoria) {}

    public function index(): View
    {
        return view('gestion.auditoria.index', [
            'registros' => $this->auditoria->paginar(15),
        ]);
    }
}

