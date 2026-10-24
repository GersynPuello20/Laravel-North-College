"""Servidor HTTP local del motor biométrico.

Solo escucha en 127.0.0.1 y exige el encabezado X-Service-Token, que debe
coincidir con FACE_SERVICE_TOKEN. No guarda imágenes ni escribe en los logs
el contenido de las peticiones.

Uso:
    set FACE_SERVICE_TOKEN=...      (el mismo valor del .env de Laravel)
    .venv\\Scripts\\python.exe server.py
"""

from __future__ import annotations

import hmac
import json
import os
import sys
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

from engine import EngineError, FaceEngine

HOST = os.environ.get("FACE_SERVICE_HOST", "127.0.0.1")
PORT = int(os.environ.get("FACE_SERVICE_PORT", "8765"))
TOKEN = os.environ.get("FACE_SERVICE_TOKEN", "")
MAX_BODY_BYTES = int(os.environ.get("FACE_SERVICE_MAX_BODY", str(8 * 1024 * 1024)))

engine = FaceEngine()


class Handler(BaseHTTPRequestHandler):
    server_version = "NorthCollegeFace/1.0"

    def log_message(self, format, *args):  # noqa: A002
        # Solo método, ruta y código; nunca el cuerpo.
        sys.stderr.write("%s %s\n" % (self.command, format % args))

    def _send(self, status: int, payload: dict) -> None:
        body = json.dumps(payload).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def _authorized(self) -> bool:
        given = self.headers.get("X-Service-Token", "")
        return bool(TOKEN) and hmac.compare_digest(given, TOKEN)

    def _read_json(self) -> dict:
        length = int(self.headers.get("Content-Length") or 0)
        if length <= 0 or length > MAX_BODY_BYTES:
            raise EngineError("invalid_request", "Tamaño de petición no válido.")
        try:
            return json.loads(self.rfile.read(length))
        except json.JSONDecodeError:
            raise EngineError("invalid_request", "JSON no válido.")

    def do_GET(self):  # noqa: N802
        if not self._authorized():
            return self._send(401, {"ok": False, "error": "unauthorized"})
        if self.path == "/health":
            return self._send(200, {"ok": True, **engine.info()})
        return self._send(404, {"ok": False, "error": "not_found"})

    def do_POST(self):  # noqa: N802
        if not self._authorized():
            return self._send(401, {"ok": False, "error": "unauthorized"})
        try:
            data = self._read_json()
            if self.path == "/detect":
                return self._send(200, engine.detect(str(data.get("image", ""))))
            if self.path == "/embed":
                frames = data.get("frames") or []
                if not isinstance(frames, list):
                    raise EngineError("invalid_request", "frames debe ser una lista.")
                direction = data.get("direction")
                if direction not in (None, "left", "right"):
                    raise EngineError("invalid_request", "direction debe ser left o right.")
                result = engine.embed([str(f) for f in frames], bool(data.get("require_liveness", True)), direction)
                return self._send(200, result)
            return self._send(404, {"ok": False, "error": "not_found"})
        except EngineError as error:
            return self._send(422, {"ok": False, "error": error.code, "message": error.message})
        except Exception:  # noqa: BLE001
            return self._send(500, {"ok": False, "error": "engine_error", "message": "Error interno del motor."})


def main() -> None:
    if not TOKEN:
        sys.exit("Falta FACE_SERVICE_TOKEN. Usa el mismo valor que en el .env de Laravel.")
    if HOST not in ("127.0.0.1", "localhost", "::1"):
        sys.stderr.write("ADVERTENCIA: el servicio escucha fuera de localhost (%s).\n" % HOST)
    server = ThreadingHTTPServer((HOST, PORT), Handler)
    sys.stderr.write("Motor biométrico en http://%s:%d (%s)\n" % (HOST, PORT, engine.info()["engine"]))
    server.serve_forever()


if __name__ == "__main__":
    main()
