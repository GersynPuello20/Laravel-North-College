<div class="card-body space-y-5">
    <x-field label="Grupo" name="course_id" required>
        <select id="course_id" name="course_id" class="form-input" required>
            <option value="">Selecciona un grupo</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @selected(old('course_id', $subject?->course_id) == $course->id)>{{ $course->name }}</option>
            @endforeach
        </select>
    </x-field>
    <x-field label="Nombre" name="name" required>
        <input id="name" name="name" value="{{ old('name', $subject?->name) }}" required maxlength="255" class="form-input">
    </x-field>
    <x-field label="Descripción" name="description">
        <textarea id="description" name="description" rows="3" class="form-input">{{ old('description', $subject?->description) }}</textarea>
    </x-field>
</div>
