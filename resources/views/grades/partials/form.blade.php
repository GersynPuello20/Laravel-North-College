<div class="card-body grid grid-cols-1 gap-5 sm:grid-cols-2">
    @if ($subjects->isEmpty())
        <p class="sm:col-span-2 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800">Tus grupos aún no tienen asignaturas. Pide a un administrador que las cree.</p>
    @endif
    <x-field label="Asignatura" name="subject_id" required class="sm:col-span-2">
        <select id="subject_id" name="subject_id" class="form-input" required>
            <option value="">Selecciona una asignatura</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" @selected(old('subject_id', $grade?->subject_id) == $subject->id)>{{ $subject->name }} · {{ $subject->course?->name }}</option>
            @endforeach
        </select>
    </x-field>
    <x-field label="Estudiante" name="student_id" required class="sm:col-span-2" hint="Solo aparecen estudiantes matriculados en tus grupos.">
        <select id="student_id" name="student_id" class="form-input" required>
            <option value="">Selecciona un estudiante</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" @selected(old('student_id', $grade?->student_id) == $student->id)>{{ $student->name }}</option>
            @endforeach
        </select>
    </x-field>
    <x-field label="Nota (0 a 100)" name="grade" required>
        <input id="grade" type="number" name="grade" step="0.01" min="0" max="100" value="{{ old('grade', $grade?->grade) }}" required class="form-input">
    </x-field>
    <x-field label="Observaciones" name="observations" class="sm:col-span-2">
        <textarea id="observations" name="observations" rows="3" maxlength="500" class="form-input">{{ old('observations', $grade?->observations) }}</textarea>
    </x-field>
</div>
