<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FaceTemplate;
use App\Services\Biometrics\FaceEngineClient;
use App\Services\Biometrics\FaceEngineUnavailable;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Configuración de solo lectura: los valores salen de config/biometrics.php
 * (variables de entorno) para que no se cambien sin dejar rastro en el despliegue.
 */
class SettingsController extends Controller
{
    public function __invoke(FaceEngineClient $engine): View
    {
        Gate::authorize('view-settings');

        try {
            $health = $engine->health();
            $engineError = null;
        } catch (FaceEngineUnavailable $error) {
            $health = null;
            $engineError = $error->getMessage();
        }

        return view('settings.index', [
            'config' => config('biometrics'),
            'health' => $health,
            'engineError' => $engineError,
            'templates' => FaceTemplate::count(),
            'audit' => AuditLog::with('user')->latest('created_at')->limit(15)->get(),
        ]);
    }
}
