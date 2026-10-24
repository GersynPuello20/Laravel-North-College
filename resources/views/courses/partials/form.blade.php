<div class="card-body space-y-5">
    <x-field label="Nombre del grupo" name="name" required>
        <input id="name" name="name" value="{{ old('name', $course?->name) }}" required maxlength="255" class="form-input">
    </x-field>

    <x-field label="Tutor" name="professor_id" hint="Solo el tutor asignado (y el personal académico) puede gestionar las sesiones del grupo.">
        <select id="professor_id" name="professor_id" class="form-input">
            <option value="">Sin asignar</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected(old('professor_id', $course?->professor_id) == $teacher->id)>{{ $teacher->name }}</option>
            @endforeach
        </select>
    </x-field>

    <x-field label="Descripción" name="description">
        <textarea id="description" name="description" rows="3" class="form-input">{{ old('description', $course?->description) }}</textarea>
    </x-field>

    @if ($course)
        <x-field label="Estado" name="status" required>
            <select id="status" name="status" class="form-input">
                @foreach (['active' => 'Activo', 'pending' => 'Pendiente', 'suspended' => 'Suspendido'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $course->status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-field>
    @endif
</div>
