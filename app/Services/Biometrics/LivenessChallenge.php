<?php

namespace App\Services\Biometrics;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Str;

/**
 * Reto de un solo uso: el servidor elige al azar hacia dónde girar la
 * cabeza y la respuesta debe llegar antes de que caduque. Así no sirve
 * reenviar una captura anterior.
 */
class LivenessChallenge
{
    private const KEY = 'biometrics.challenges';

    public function __construct(private Session $session) {}

    public function issue(string $context): array
    {
        $challenge = [
            'id' => (string) Str::uuid(),
            'context' => $context,
            'direction' => random_int(0, 1) === 0 ? 'left' : 'right',
            'expires_at' => now()->addSeconds(config('biometrics.challenge_ttl'))->getTimestamp(),
        ];

        $pending = collect($this->session->get(self::KEY, []))
            ->filter(fn ($item) => $item['expires_at'] > now()->getTimestamp())
            ->put($challenge['id'], $challenge)
            ->take(-5);

        $this->session->put(self::KEY, $pending->all());

        return [
            'id' => $challenge['id'],
            'direction' => $challenge['direction'],
            'expires_in' => config('biometrics.challenge_ttl'),
        ];
    }

    /** Devuelve la dirección pedida, o null si el reto no existe, caducó o es de otro contexto. */
    public function consume(?string $id, string $context): ?string
    {
        if (! $id) {
            return null;
        }

        $pending = $this->session->get(self::KEY, []);
        $challenge = $pending[$id] ?? null;
        unset($pending[$id]);
        $this->session->put(self::KEY, $pending);

        if (! $challenge || $challenge['context'] !== $context || $challenge['expires_at'] < now()->getTimestamp()) {
            return null;
        }

        return $challenge['direction'];
    }
}
