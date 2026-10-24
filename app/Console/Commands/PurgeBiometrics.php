<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\FaceTemplate;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

/**
 * Elimina los vectores biométricos que ya no tienen finalidad:
 *  - sin autorización activa (no debería pasar: revocar ya los borra),
 *  - de estudiantes inactivos desde hace más de N días,
 *  - de estudiantes sin ninguna matrícula activa desde hace más de N días.
 *
 * No existe una fecha exacta de "dejó de estar activo", así que se usa la
 * última modificación del usuario o de sus matrículas como aproximación.
 */
class PurgeBiometrics extends Command
{
    protected $signature = 'biometrics:purge {--dry-run : Solo muestra cuántos vectores se eliminarían}';

    protected $description = 'Elimina vectores faciales sin autorización activa o de estudiantes que ya no están activos';

    public function handle(): int
    {
        $days = max(0, (int) config('biometrics.retention_days'));
        $cutoff = now()->subDays($days);

        // Sin autorización activa se borra siempre; el resto espera el plazo de retención.
        $query = FaceTemplate::query()
            ->where(function (Builder $q) use ($cutoff) {
                $q->whereDoesntHave('student.biometricConsents', fn ($c) => $c->active())
                    ->orWhere(fn (Builder $old) => $old
                        ->where('created_at', '<', $cutoff)
                        ->where(fn (Builder $w) => $w
                            ->whereHas('student', fn ($s) => $s->where('status', '!=', User::STATUS_ACTIVE)->where('updated_at', '<', $cutoff))
                            ->orWhereHas('student', fn ($s) => $s
                                ->whereDoesntHave('enrollments', fn ($e) => $e->where('status', 'active'))
                                ->whereDoesntHave('enrollments', fn ($e) => $e->where('updated_at', '>=', $cutoff)))));
            });

        $templates = $query->get(['id', 'student_id']);

        if ($this->option('dry-run')) {
            $this->info("Se eliminarían {$templates->count()} vectores (retención: {$days} días).");

            return self::SUCCESS;
        }

        foreach ($templates as $template) {
            $template->delete();
            AuditLog::record('biometric.template_purged', $template, [
                'student_id' => $template->student_id,
                'retention_days' => $days,
            ]);
        }

        $this->info("Vectores eliminados: {$templates->count()}.");

        return self::SUCCESS;
    }
}
