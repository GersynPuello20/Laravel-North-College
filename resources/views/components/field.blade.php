@props(['label', 'name', 'hint' => null, 'required' => false])

<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if ($required)
            <span class="text-accent-500" aria-hidden="true">*</span>
        @endif
    </label>
    {{ $slot }}
    @if ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
