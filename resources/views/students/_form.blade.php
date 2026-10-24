<div class="grid gap-6">
    <div>
        <label class="block text-sm font-medium text-gray-700">Nombre</label>
        <input type="text" name="name" value="{{ old('name', $student->name ?? '') }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500" required>
        @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" value="{{ old('email', $student->email ?? '') }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500" required>
        @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Matrícula</label>
        <input type="text" name="enrollment_number" value="{{ old('enrollment_number', $student->enrollment_number ?? '') }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500" required>
        @error('enrollment_number') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Teléfono</label>
        <input type="text" name="phone" value="{{ old('phone', $student->phone ?? '') }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-slate-50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-blue-500">
        @error('phone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
