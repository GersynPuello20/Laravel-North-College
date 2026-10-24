{{-- Vista de cámara compartida. Requiere un ancestro con x-data="facialCapture(...)". --}}
<div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-900">
    <div class="relative aspect-[4/3] w-full">
        <video x-ref="video" playsinline muted class="absolute inset-0 h-full w-full -scale-x-100 object-cover" x-show="camera === 'ready'"></video>
        <canvas x-ref="canvas" class="hidden"></canvas>

        {{-- Sin cámara --}}
        <div x-show="camera !== 'ready'" class="absolute inset-0 flex flex-col items-center justify-center gap-4 p-6 text-center text-slate-300">
            <x-icon name="video-camera-slash" class="size-10 text-slate-500" />
            <p class="max-w-sm text-sm" x-text="guide.message"></p>
            <button type="button" class="btn-primary" @click="startCamera()" x-show="camera !== 'starting'">
                <x-icon name="camera" class="size-4" /> Activar cámara
            </button>
        </div>

        {{-- Guía para colocar el rostro --}}
        <template x-if="camera === 'ready'">
            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                <div class="h-[70%] w-[46%] rounded-[50%] border-4 transition-colors"
                     :class="{
                        'border-emerald-400': guide.ok && phase !== 'processing',
                        'border-white/60 border-dashed': !guide.ok && phase === 'idle',
                        'border-sky-400': phase === 'challenge',
                        'border-amber-300 animate-pulse': phase === 'processing',
                        'border-white/60': phase === 'result',
                     }"></div>
            </div>
        </template>

        {{-- Indicador de detección --}}
        <div x-show="camera === 'ready'" class="absolute left-3 top-3 flex items-center gap-2 rounded-full bg-slate-900/70 px-3 py-1.5 text-xs text-white backdrop-blur">
            <span class="size-2 rounded-full" :class="phase === 'processing' ? 'bg-amber-400 animate-pulse' : (guide.ok ? 'bg-emerald-400' : 'bg-slate-400')"></span>
            <span x-text="phase === 'processing' ? 'Procesando' : (guide.ok ? 'Rostro detectado' : 'Buscando rostro')"></span>
        </div>

        {{-- Instrucción del reto --}}
        <div x-show="phase === 'challenge' || phase === 'processing'" x-cloak class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/90 to-transparent px-4 pb-5 pt-12 text-center">
            <div class="mb-2 flex justify-center gap-1.5">
                <template x-for="n in 3">
                    <span class="h-1.5 w-8 rounded-full" :class="step >= n || phase === 'processing' ? 'bg-white' : 'bg-white/30'"></span>
                </template>
            </div>
            <p class="flex items-center justify-center gap-2 text-lg font-semibold text-white">
                <span x-show="step === 2 && direction === 'left'" aria-hidden="true">←</span>
                <span x-text="instruction"></span>
                <span x-show="step === 2 && direction === 'right'" aria-hidden="true">→</span>
            </p>
        </div>
    </div>

    <div x-show="camera === 'ready'" class="flex flex-wrap items-center justify-between gap-3 bg-white px-4 py-3">
        <p class="flex min-w-0 items-center gap-2 text-sm" :class="guide.ok ? 'text-emerald-700' : 'text-slate-600'">
            <x-icon name="information-circle" class="size-4 shrink-0" />
            <span class="truncate" x-text="guide.message"></span>
        </p>
        <div class="flex gap-2">
            <button type="button" class="btn-ghost btn-sm" @click="stopCamera()">Apagar cámara</button>
            <button type="button" class="btn-primary" @click="run()" :disabled="!canStart">
                <x-icon name="finger-print" class="size-4" />
                <span x-text="phase === 'result' ? 'Nueva captura' : '{{ $action ?? 'Iniciar captura' }}'"></span>
            </button>
        </div>
    </div>
</div>
