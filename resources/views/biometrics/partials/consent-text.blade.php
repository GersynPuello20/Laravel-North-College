{{-- Texto de la autorización. Si cambia, actualizar BIOMETRIC_CONSENT_VERSION. Requiere revisión jurídica (docs/PROTECCION-DATOS.md). --}}
<div class="space-y-3 text-sm leading-relaxed text-slate-600">
    <p><strong class="text-slate-800">Responsable:</strong> {{ config('biometrics.controller_name') }}.
        @if (config('biometrics.contact_email'))
            Canal para consultas, reclamos y retiro de la autorización: <span class="font-medium">{{ config('biometrics.contact_email') }}</span>.
        @else
            <span class="font-medium text-amber-700">Canal de atención pendiente de definir (BIOMETRIC_CONTACT_EMAIL).</span>
        @endif
    </p>
    <p><strong class="text-slate-800">Finalidad:</strong> verificar la identidad del estudiante para registrar su asistencia a las sesiones de clase. No se usa para ningún otro fin.</p>
    <p><strong class="text-slate-800">Qué se guarda:</strong> una representación numérica del rostro (vector biométrico), cifrada. No se guardan fotografías: las imágenes de la cámara se procesan en memoria y se descartan.</p>
    <p><strong class="text-slate-800">Dato sensible y voluntario:</strong> los datos biométricos son datos sensibles (Ley 1581 de 2012). Nadie está obligado a autorizar su tratamiento y negarse no afecta ninguna actividad académica: la asistencia se puede registrar siempre de forma manual.</p>
    <p><strong class="text-slate-800">Menores de edad:</strong> la autorización la da su representante legal, después de escuchar la opinión del menor, y debe responder a su interés superior.</p>
    <p><strong class="text-slate-800">Derechos:</strong> conocer, actualizar y rectificar los datos, solicitar prueba de la autorización, ser informado del uso, presentar quejas ante la Superintendencia de Industria y Comercio, y revocar la autorización o pedir la supresión en cualquier momento.</p>
    <p><strong class="text-slate-800">Conservación:</strong> el vector se elimina de inmediato al revocar la autorización, y a los {{ config('biometrics.retention_days') }} días de que el estudiante quede sin matrícula activa o con la cuenta inactiva.</p>
    <p class="text-xs text-slate-400">Versión del texto: {{ config('biometrics.consent_version') }}</p>
</div>
