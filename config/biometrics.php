<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Servicio local de reconocimiento facial
    |--------------------------------------------------------------------------
    |
    | Microservicio Python en facial-service/ (OpenCV YuNet + SFace). Solo
    | debe escuchar en localhost. El token se comparte con el servicio por la
    | variable FACE_SERVICE_TOKEN y nunca se envía al navegador.
    |
    */

    'service_url' => env('FACE_SERVICE_URL', 'http://127.0.0.1:8765'),
    'service_token' => env('FACE_SERVICE_TOKEN'),
    'timeout' => (int) env('FACE_SERVICE_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | Umbrales de comparación (similitud coseno, de -1 a 1)
    |--------------------------------------------------------------------------
    |
    | match_threshold: 0.363 es el valor de referencia que OpenCV publica para
    | SFace (evaluado en LFW). NO está validado con la población de North
    | College: hay que calibrarlo con datos propios antes de usarlo en serio.
    |
    | unknown_threshold y ambiguity_margin son decisiones de experiencia de
    | uso, no valores validados: por debajo de unknown_threshold se informa
    | "rostro desconocido"; entre ambos umbrales, "baja confianza". Si los dos
    | mejores candidatos están a menos de ambiguity_margin, no se registra.
    |
    */

    'match_threshold' => (float) env('FACE_MATCH_THRESHOLD', 0.363),
    'unknown_threshold' => (float) env('FACE_UNKNOWN_THRESHOLD', 0.25),
    'ambiguity_margin' => (float) env('FACE_AMBIGUITY_MARGIN', 0.05),

    /*
    |--------------------------------------------------------------------------
    | Prueba de presencia (liveness)
    |--------------------------------------------------------------------------
    |
    | Reto de giro de cabeza en una dirección aleatoria que caduca a los
    | challenge_ttl segundos y solo se puede usar una vez. Reduce el riesgo
    | de una foto fija, pero NO detiene videos, pantallas ni fotos inclinadas.
    | Ver docs/RECONOCIMIENTO-FACIAL.md.
    |
    */

    'liveness_required' => (bool) env('FACE_LIVENESS_REQUIRED', true),
    'challenge_ttl' => (int) env('FACE_CHALLENGE_TTL', 90),
    'max_frames' => 12,

    /*
    |--------------------------------------------------------------------------
    | Autorización de tratamiento de datos (Ley 1581 de 2012)
    |--------------------------------------------------------------------------
    |
    | Cambiar consent_version cuando cambie el texto de la autorización. Los
    | datos del responsable deben completarse y revisarse jurídicamente.
    |
    */

    'consent_version' => env('BIOMETRIC_CONSENT_VERSION', '2026-10-v1'),
    'controller_name' => env('BIOMETRIC_CONTROLLER_NAME', 'North College'),
    'contact_email' => env('BIOMETRIC_CONTACT_EMAIL'),
    'adult_age' => 18,

    /*
    | Días que se conserva un vector biométrico después de que el estudiante
    | queda inactivo o sin matrículas activas. Al revocar la autorización el
    | vector se elimina de inmediato.
    */
    'retention_days' => (int) env('BIOMETRIC_RETENTION_DAYS', 30),

];
