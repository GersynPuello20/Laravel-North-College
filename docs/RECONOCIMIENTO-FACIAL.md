# Reconocimiento facial en North College

Este documento explica cómo funciona la asistencia por reconocimiento facial, qué
garantiza, qué **no** garantiza y cómo probarlo con una cámara real.

> El reconocimiento facial es un método **opcional** de registro. La asistencia
> manual existe siempre, para cualquier estudiante, sin condiciones.

## 1. Arquitectura

```
Navegador (cámara)          Laravel (North College)                 facial-service (Python, local)
──────────────────          ────────────────────────                ──────────────────────────────
getUserMedia ──JPEG──▶  FaceCaptureRequest (valida)  ──HTTP──▶  /detect  calidad del fotograma
                        LivenessChallenge (reto)      token     /embed   calidad + presencia + vector
                        FaceMatcher (coseno)  ◀───vector───────
                        AttendanceService (registro)
                        face_templates (cifrado)
```

- **facial-service/** es un servicio HTTP sin estado que escucha solo en
  `127.0.0.1:8765` y exige el encabezado `X-Service-Token`. No guarda imágenes
  ni vectores, y no registra el cuerpo de las peticiones.
- **Laravel** decide todo lo demás: quién es candidato, el umbral, si se
  registra la asistencia y qué queda auditado.
- Modelos (OpenCV Zoo, se descargan con `download_models.py` y se verifica su SHA-256):
  - **YuNet 2023mar** (detección y 5 puntos faciales). Licencia MIT.
  - **SFace 2021dec** (vector de 128 dimensiones). Licencia Apache 2.0.

## 2. Flujo

### Enrolamiento (solo personal: admin o coordinación)
1. Ficha del estudiante → *Datos biométricos*.
2. Se registra la **autorización** (ver `PROTECCION-DATOS.md`). Sin ella, el
   botón de captura no aparece y la API responde `409 no_consent`.
3. El servidor emite un **reto** de un solo uso (girar la cabeza a la izquierda
   o a la derecha, al azar, válido 90 s).
4. La cámara envía 8 fotogramas: 2 de frente, 4 girando y 2 de frente.
5. El motor valida calidad y presencia y devuelve el promedio normalizado de
   los vectores frontales.
6. Laravel comprueba que ese rostro **no coincida con otro estudiante ya
   enrolado** (si coincide: `duplicate_identity`) y guarda el vector cifrado.

### Toma de asistencia (tutor del grupo o personal)
1. Sesión abierta → *Asistencia facial*.
2. Mismo reto y misma captura.
3. **Candidatos**: solo los estudiantes con matrícula activa en el grupo de la
   sesión, con autorización vigente y vector enrolado. Nunca toda la institución.
4. Resultado:

| Resultado | Condición | ¿Registra? |
|---|---|---|
| `matched` | mejor puntaje ≥ 0.363 y ventaja ≥ 0.05 sobre el segundo | Sí (método *facial*, con puntaje) |
| `ambiguous` | mejor puntaje ≥ 0.363 pero el segundo está a menos de 0.05 | **No** |
| `low_confidence` | 0.25 ≤ puntaje < 0.363 | **No** |
| `unknown` | puntaje < 0.25 | **No** |
| `already_registered` | el estudiante ya tenía registro en la sesión | No duplica |
| `liveness_failed`, `capture_failed`, `engine_unavailable`, `no_candidates` | — | **No**; se sugiere el registro manual |

Solo en `matched` la respuesta revela el nombre del estudiante. Todos los
intentos quedan en `face_recognition_attempts` (resultado, puntaje, quién lo
hizo) **sin imágenes**.

**Un rostro no reconocido nunca marca ausencia.** La ausencia sale solo de cerrar
la sesión: los matriculados sin registro quedan como *Ausente* (método
`session_close`).

## 3. Umbrales y por qué

| Variable | Valor | Justificación |
|---|---|---|
| `FACE_MATCH_THRESHOLD` | **0.363** | Umbral de similitud coseno que publica OpenCV para SFace (ejemplo oficial `face_recognition_sface`, calibrado en LFW). **No está validado con la población de North College.** |
| `FACE_UNKNOWN_THRESHOLD` | 0.25 | Separa "parecido pero insuficiente" de "nadie". Es una decisión de producto, no viene del modelo. |
| `FACE_AMBIGUITY_MARGIN` | 0.05 | Si dos estudiantes quedan a menos de 0.05, no se elige ninguno. Decisión de producto. |
| `FACE_MIN_DETECTION_SCORE` | 0.9 | Valor de los ejemplos de YuNet. |
| `FACE_MIN_FACE_WIDTH` | 80 px | Por debajo, el vector pierde calidad. |
| `FACE_MIN/MAX_BRIGHTNESS` | 40–220 | Rechaza capturas muy oscuras o quemadas. |
| `FACE_MIN_SHARPNESS` | 30 | Varianza del laplaciano; filtra imágenes movidas. |
| `FACE_FRONTAL_MAX_YAW` / `FACE_TURNED_MIN_YAW` | 0.18 / 0.30 | Prueba de presencia (ver §4). Medidos con imágenes de prueba, no con una cámara del aula. |

**Recomendación antes de usarlo en serio:** hacer una calibración local con
estudiantes que lo autoricen (pares misma persona / personas distintas, con la
cámara y la luz reales del aula), medir falsos positivos y falsos negativos, y
ajustar `FACE_MATCH_THRESHOLD`. Mientras no exista esa medición, el sistema
prefiere **no registrar** antes que registrar a la persona equivocada.

## 4. Prueba de presencia (liveness): qué hace y sus límites

**Qué hace:** pide girar la cabeza hacia una dirección elegida al azar por el
servidor. El giro se estima con el desplazamiento horizontal de la nariz
respecto al centro de los ojos, dividido por la distancia entre ojos. Además
exige que todos los fotogramas sean de la misma persona (similitud ≥ 0.30) y
que el reto no se haya usado ni haya caducado.

**Qué frena:** una foto impresa o una pantalla con una imagen fija, y reenviar
una captura anterior (el reto es de un solo uso).

**Qué NO frena (limitaciones comprobadas o conocidas):**
- **Medido:** una foto frontal comprimida horizontalmente al 80 % dio un giro
  estimado de 0.26. Inclinar o deformar una foto frente a la cámara puede acercarse
  al umbral de 0.30 y, con algo de práctica, superarlo.
- Un **video** de la persona girando la cabeza en la dirección pedida.
- Máscaras o reconstrucciones 3D.
- No hay detección de profundidad ni de textura (anti-spoofing por hardware o por modelo).

**Conclusión:** esta prueba **no es infalible** y no debe presentarse como tal.
Sirve para subir el costo de un fraude casual. La mitigación real es operativa:
el tutor está presente durante la toma, el registro facial queda marcado con
método y puntaje, y cualquier registro puede corregirse con motivo y trazabilidad.
Para entornos de mayor riesgo habría que integrar un modelo anti-spoofing
dedicado o una cámara con profundidad.

## 5. Seguridad del dato biométrico

- El vector se guarda en `face_templates.embedding` cifrado con `APP_KEY`
  (cast `encrypted:array`). Está en `$hidden`: nunca se serializa a JSON.
- **No se guardan fotos**: los fotogramas viajan en memoria del navegador a
  Laravel y al motor, y se descartan.
- Los logs no incluyen imágenes, vectores ni el token.
- Rutas biométricas: solo admin/coordinación (gate `manage-biometrics`); la
  toma facial, solo el tutor del grupo o el personal (policy `manage`).
- Límite de peticiones: 30/min para capturas y retos, 90/min para la detección
  de guía.
- Al **revocar** la autorización, el vector se borra en la misma transacción.
- `php artisan biometrics:purge` (programado a las 02:00) borra los vectores
  sin autorización vigente y los de estudiantes inactivos o sin matrícula
  activa después de `BIOMETRIC_RETENTION_DAYS`.
- **Si se pierde o cambia `APP_KEY`, los vectores no se pueden descifrar** y hay
  que volver a enrolar. Respaldar `APP_KEY` de forma segura.

## 6. Cómo probar con una cámara real

Requisitos: Windows con Python 3.10+ (`py`), PHP 8.2, Node. La cámara exige
`http://localhost` o `127.0.0.1` (o HTTPS en otro dominio).

```bat
:: 1. Motor biométrico (primera vez crea el venv, instala y descarga modelos)
facial-service\start.bat

:: 2. Laravel (otra terminal)
php artisan serve
npm run dev          :: o npm run build
```

1. En `.env` debe existir `FACE_SERVICE_TOKEN` (el mismo que lee `start.bat`).
   Para generar uno: `php -r "echo bin2hex(random_bytes(32));"`.
2. Opcional: `php artisan db:seed --class=DemoSeeder` (grupo, tutor y 3
   estudiantes de demostración; uno es menor de edad).
3. Entra como `admin@northcollege.test` → **Configuración**: debe decir
   *Conectado* con la versión de OpenCV.
4. **Estudiantes** → elige uno → **Datos biométricos** → registra la
   autorización → **Iniciar cámara** → sigue las indicaciones (mirar de frente,
   girar hacia donde diga, volver al frente).
5. Entra como `tutor.demo@northcollege.test` → **Sesiones** → abre la sesión
   de hoy → **Asistencia facial** → la persona enrolada se pone frente a la
   cámara. Debe aparecer "Asistencia registrada" y el método *Facial* en la sesión.
6. Prueba negativa: una persona no enrolada, o una foto en el celular, deben
   terminar en *No reconocido* o *Falló la prueba de presencia*, sin registro.
7. Cierra la sesión: quien no tenga registro queda *Ausente*.

**Probado en este entorno:** el motor con imágenes de prueba de OpenCV
(`lena.jpg`, `messi5.jpg`) y las pruebas automáticas con el motor simulado.
**No probado aquí:** una cámara web real en el navegador. El flujo de cámara
está implementado, pero hay que verificarlo siguiendo estos pasos.
