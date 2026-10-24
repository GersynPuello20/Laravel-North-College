<?php

namespace App\Services\Biometrics;

/** Traduce los códigos del motor a mensajes que el usuario pueda corregir. */
class CaptureMessages
{
    private const MESSAGES = [
        'no_face' => 'No se detectó ningún rostro. Colócate dentro de la guía.',
        'multiple_faces' => 'Hay más de un rostro en la imagen. Debe aparecer una sola persona.',
        'face_too_small' => 'El rostro está muy lejos. Acércate a la cámara.',
        'too_dark' => 'Hay poca luz. Busca un lugar más iluminado.',
        'too_bright' => 'Hay demasiada luz o un reflejo sobre el rostro.',
        'blurry' => 'La imagen está borrosa. Quédate quieto un momento.',
        'low_detection_score' => 'El rostro no se ve con claridad.',
        'low_quality' => 'La captura no tiene calidad suficiente. Revisa la luz y la distancia.',
        'no_frontal_face' => 'Mira de frente a la cámara al empezar y al terminar.',
        'liveness_failed' => 'No se detectó el giro de cabeza solicitado. Repite el movimiento.',
        'invalid_image' => 'No se pudo leer la imagen de la cámara.',
        'too_many_frames' => 'La captura enviada no es válida.',
        'no_frames' => 'No se recibieron imágenes de la cámara.',
    ];

    public static function for(array $response): string
    {
        $code = $response['error'] ?? 'low_quality';

        if ($code === 'low_quality' && ! empty($response['reasons'])) {
            $first = $response['reasons'][0];
            if (isset(self::MESSAGES[$first])) {
                return self::MESSAGES[$first];
            }
        }

        if ($code === 'liveness_failed' && ($response['liveness']['reason'] ?? null) === 'inconsistent_identity') {
            return 'Los fotogramas no parecen de la misma persona. Repite la captura.';
        }

        return self::MESSAGES[$code] ?? 'La captura no es válida. Inténtalo de nuevo.';
    }

    public static function issue(string $code): ?string
    {
        return self::MESSAGES[$code] ?? null;
    }
}
