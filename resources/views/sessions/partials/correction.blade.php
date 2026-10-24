<div x-data="{ open: false }" class="inline-block text-left">
    <button type="button" class="btn-ghost btn-sm" @click="open = true">
        <x-icon name="pencil-square" class="size-4" /> Corregir
    </button>

    <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" @keydown.escape.window="open = false">
        <div class="card w-full max-w-md text-left" @click.outside="open = false" role="dialog" aria-modal="true" aria-labelledby="correction-title-{{ $record->id }}">
            <form method="POST" action="{{ route('attendances.update', $record) }}">
                @csrf @method('PUT')
                <div class="card-header">
                    <h3 id="correction-title-{{ $record->id }}" class="card-title">Corregir asistencia · {{ $record->student?->name }}</h3>
                </div>
                <div class="card-body space-y-4">
                    <p class="text-sm text-slate-600">Estado actual: <x-attendance-badge :status="$record->status" /></p>

                    <div>
                        <label for="status-{{ $record->id }}" class="form-label">Nuevo estado</label>
                        <select id="status-{{ $record->id }}" name="status" class="form-input" required>
                            @foreach (\App\Models\SessionAttendance::STATUS_LABELS as $value => $label)
                                @continue($value === $record->status)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="reason-{{ $record->id }}" class="form-label">Motivo</label>
                        <textarea id="reason-{{ $record->id }}" name="reason" rows="3" minlength="5" maxlength="500" required class="form-input" placeholder="Ej.: presentó excusa médica"></textarea>
                        <p class="form-hint">Queda guardado con tu nombre y la fecha.</p>
                    </div>

                    @if ($record->corrections->isNotEmpty())
                        <div>
                            <p class="form-label">Historial</p>
                            <ul class="space-y-1 text-xs text-slate-500">
                                @foreach ($record->corrections as $correction)
                                    <li>{{ $correction->created_at->format('d/m H:i') }} · {{ $correction->changedBy?->name }}:
                                        {{ \App\Models\SessionAttendance::STATUS_LABELS[$correction->old_status] ?? $correction->old_status }} →
                                        {{ \App\Models\SessionAttendance::STATUS_LABELS[$correction->new_status] ?? $correction->new_status }}
                                        — {{ $correction->reason }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
                    <button type="button" class="btn-secondary" @click="open = false">Cancelar</button>
                    <button class="btn-primary">Guardar corrección</button>
                </div>
            </form>
        </div>
    </div>
</div>
