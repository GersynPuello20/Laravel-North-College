<?php

namespace App\Services\Biometrics;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Cliente HTTP del servicio local facial-service. Las imágenes viajan solo
 * en memoria; este cliente no las registra ni las guarda.
 */
class FaceEngineClient
{
    public function health(): array
    {
        return $this->send(fn (PendingRequest $http) => $http->timeout(3)->get('/health'));
    }

    public function detect(string $image): array
    {
        return $this->send(fn (PendingRequest $http) => $http->post('/detect', ['image' => $image]));
    }

    /**
     * @param  list<string>  $frames  imágenes JPEG en base64
     */
    public function embed(array $frames, bool $requireLiveness, ?string $direction = null): array
    {
        return $this->send(fn (PendingRequest $http) => $http->post('/embed', [
            'frames' => $frames,
            'require_liveness' => $requireLiveness,
            'direction' => $direction,
        ]));
    }

    private function send(callable $call): array
    {
        $token = config('biometrics.service_token');

        if (! $token) {
            throw new FaceEngineUnavailable('Falta configurar FACE_SERVICE_TOKEN.');
        }

        $http = Http::baseUrl(rtrim(config('biometrics.service_url'), '/'))
            ->timeout(config('biometrics.timeout'))
            ->acceptJson()
            ->withHeaders(['X-Service-Token' => $token]);

        try {
            $response = $call($http);
        } catch (ConnectionException) {
            throw new FaceEngineUnavailable('No se pudo conectar con el servicio de reconocimiento facial.');
        }

        if ($response->status() === 401) {
            throw new FaceEngineUnavailable('El servicio de reconocimiento rechazó el token configurado.');
        }

        if ($response->serverError() || ! is_array($response->json())) {
            throw new FaceEngineUnavailable('El servicio de reconocimiento respondió con un error.');
        }

        return $response->json();
    }
}
