# North College

Sistema de gestión académica con control de asistencia por sesión de clase y
registro opcional por reconocimiento facial.

- **Asistencia por sesión**: grupos, sesiones (manuales o generadas desde los
  horarios), apertura y cierre, estados *Presente / Tarde / Ausente / Ausencia
  justificada*, método de registro y correcciones con motivo y responsable.
- **Reconocimiento facial opcional**: autorización explícita (Ley 1581 de
  2012), enrolamiento con prueba de presencia, identificación solo entre los
  estudiantes del grupo, y registro manual siempre disponible.
- **Informes**: filtros por fecha, grupo, tutor, estudiante, estado y método;
  exportación a Excel, CSV y PDF.
- **Roles**: superadministrador, administrador, coordinación, tutor (docente),
  estudiante y pendiente.

Stack: Laravel 12 · PHP 8.2 · Blade + Tailwind 3 + Alpine · Vite · SQLite/MySQL ·
servicio local Python (OpenCV YuNet + SFace).

## Instalación

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Token compartido con el motor biométrico:
php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"   # pegarlo en FACE_SERVICE_TOKEN
php artisan migrate --seed
npm run build
```

Usuario inicial: `admin@northcollege.test` / `Password123!` (cámbialo).

Datos de demostración opcionales (un grupo, un tutor y tres estudiantes):

```bash
php artisan db:seed --class=DemoSeeder
```

## Ejecutar

```bash
php artisan serve                 # http://127.0.0.1:8000
facial-service\start.bat          # motor biométrico en 127.0.0.1:8765 (Windows)
php artisan schedule:work         # tareas programadas (purga biométrica diaria)
```

En Linux/macOS el motor se inicia con:

```bash
cd facial-service
python3 -m venv .venv && .venv/bin/pip install -r requirements.txt
.venv/bin/python download_models.py
FACE_SERVICE_TOKEN=... .venv/bin/python server.py
```

## Pruebas

```bash
php artisan test
# Motor biométrico (las pruebas con rostros necesitan imágenes de muestra de OpenCV):
facial-service\.venv\Scripts\python -m unittest discover -s facial-service\tests
```

## Variables de entorno nuevas

| Variable | Por defecto | Uso |
|---|---|---|
| `APP_TIMEZONE` | `America/Bogota` | Hora de las sesiones y de los registros |
| `FACE_SERVICE_URL` | `http://127.0.0.1:8765` | Dirección del motor |
| `FACE_SERVICE_TOKEN` | — | Secreto compartido con el motor (obligatorio) |
| `FACE_SERVICE_TIMEOUT` | `15` | Segundos de espera |
| `FACE_MATCH_THRESHOLD` | `0.363` | Similitud mínima para registrar |
| `FACE_UNKNOWN_THRESHOLD` | `0.25` | Por debajo, "no reconocido" |
| `FACE_AMBIGUITY_MARGIN` | `0.05` | Ventaja mínima sobre el segundo candidato |
| `FACE_LIVENESS_REQUIRED` | `true` | Exigir el reto de giro de cabeza |
| `FACE_CHALLENGE_TTL` | `90` | Vigencia del reto en segundos |
| `BIOMETRIC_CONSENT_VERSION` | `2026-10-v1` | Versión del texto de autorización |
| `BIOMETRIC_CONTROLLER_NAME` | `North College` | Responsable del tratamiento |
| `BIOMETRIC_CONTACT_EMAIL` | — | Canal para consultas y revocatorias |
| `BIOMETRIC_RETENTION_DAYS` | `30` | Retención tras inactividad |

El motor acepta además `FACE_MIN_DETECTION_SCORE`, `FACE_MIN_FACE_WIDTH`,
`FACE_MIN_BRIGHTNESS`, `FACE_MAX_BRIGHTNESS`, `FACE_MIN_SHARPNESS`,
`FACE_FRONTAL_MAX_YAW`, `FACE_TURNED_MIN_YAW` y `FACE_CONSISTENCY_THRESHOLD`.

## Documentación

- [Reconocimiento facial: arquitectura, umbrales, límites y prueba con cámara](docs/RECONOCIMIENTO-FACIAL.md)
- [Protección de datos (Ley 1581 de 2012) y puntos a validar jurídicamente](docs/PROTECCION-DATOS.md)
