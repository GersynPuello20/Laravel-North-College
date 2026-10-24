@php $currentDay = \App\Models\Schedule::normalizeDay(old('day_of_week', $schedule?->day_of_week)); @endphp

<div class="card-body grid grid-cols-1 gap-5 sm:grid-cols-2">
    <x-field label="Grupo" name="course_id" required class="sm:col-span-2">
        <select id="course_id" name="course_id" class="form-input" required>
            <option value="">Selecciona un grupo</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @selected(old('course_id', $schedule?->course_id) == $course->id)>{{ $course->name }}</option>
            @endforeach
        </select>
    </x-field>
    <x-field label="Día" name="day_of_week" required>
        <select id="day_of_week" name="day_of_week" class="form-input" required>
            <option value="">Selecciona un día</option>
            @foreach (\App\Models\Schedule::DAY_LABELS as $value => $label)
                <option value="{{ $value }}" @selected($currentDay === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </x-field>
    <x-field label="Aula" name="classroom" required>
        <input id="classroom" name="classroom" value="{{ old('classroom', $schedule?->classroom) }}" required maxlength="80" class="form-input">
    </x-field>
    <x-field label="Hora de inicio" name="start_time" required>
        <input id="start_time" type="time" name="start_time" value="{{ old('start_time', $schedule?->start_time?->format('H:i')) }}" required class="form-input">
    </x-field>
    <x-field label="Hora de fin" name="end_time" required>
        <input id="end_time" type="time" name="end_time" value="{{ old('end_time', $schedule?->end_time?->format('H:i')) }}" required class="form-input">
    </x-field>
</div>
