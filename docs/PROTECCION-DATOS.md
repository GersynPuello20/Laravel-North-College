# Protección de datos biométricos (Colombia)

Este documento resume cómo North College aplica el régimen colombiano de datos
personales al módulo de asistencia facial. **No es un concepto jurídico.** Lo
marcado como ⚠️ necesita revisión de un abogado o del oficial de protección de
datos de la institución antes de usar el sistema con estudiantes reales.

Normas de referencia: **Ley 1581 de 2012** (régimen general; art. 5 y 6 datos
sensibles, art. 7 niños, niñas y adolescentes, art. 9 autorización, art. 8
derechos del titular) y **Decreto 1377 de 2013**, compilado en el **Decreto
1074 de 2015** (autorización, menores, aviso de privacidad, políticas de
tratamiento).

## 1. Cómo lo aplica el sistema

| Principio / exigencia | Implementación | Dónde |
|---|---|---|
| **Dato sensible** (biometría, art. 5) | El vector se trata como sensible: cifrado con `APP_KEY`, oculto en la serialización, acceso solo para el personal autorizado. | `FaceTemplate`, gate `manage-biometrics` |
| **Finalidad** (art. 4 b) | Una sola finalidad declarada: verificar identidad para la asistencia. El vector no se usa para nada más y solo se compara contra los estudiantes del grupo de la sesión. | Texto de autorización, `FacialAttendanceService` |
| **Autorización previa, expresa e informada** (art. 9) | Sin autorización vigente no hay captura (`409 no_consent`). El formulario exige confirmar que se informó y que es voluntaria. | `BiometricController::storeConsent` |
| **Facultativa** (art. 6 a) | Negarse no afecta nada: el registro manual siempre está disponible y es el mismo flujo de asistencia. | Sesiones → registro manual |
| **Prueba de la autorización** (Decreto 1377, art. 7–8) | Se guarda versión del texto, fecha, quién autoriza (nombre, documento, parentesco), quién la registró y si se escuchó al menor. | `biometric_consents` |
| **Revocatoria y supresión** (art. 8 e) | El estudiante puede revocar desde *Mis datos biométricos*; el personal también, con motivo. Al revocar, el vector se borra en la misma transacción. | `BiometricConsentService::revoke` |
| **Menores de edad** (art. 7; Decreto 1377, art. 12) | Si el estudiante es menor, solo autoriza su representante legal (madre, padre o tutor legal) y se exige constancia de que se escuchó al menor. Sin fecha de nacimiento no se puede autorizar. | `BiometricConsentService::grant` |
| **Circulación restringida / seguridad** (art. 4 g y h) | Servicio biométrico solo en `localhost` con token; no se guardan fotos; logs sin imágenes ni vectores; límites de peticiones; auditoría de cada acción. | `facial-service`, `audit_logs` |
| **Temporalidad** | Purga diaria: vectores sin autorización vigente se borran siempre; los de estudiantes inactivos o sin matrícula activa, a los `BIOMETRIC_RETENTION_DAYS` días (30 por defecto). | `biometrics:purge` |
| **Derechos del titular** (art. 8) | El texto de la autorización los enumera; el estudiante ve en su panel qué se guarda, desde cuándo y su historial de autorizaciones. | `biometrics.mine` |

## 2. Qué queda registrado

- `biometric_consents`: historial completo, incluidas las revocadas (prueba de autorización).
- `face_templates`: un vector por estudiante, cifrado. Sin fotos.
- `face_recognition_attempts`: resultado, puntaje y quién operó. Sin imágenes. En
  intentos no concluyentes no se guarda a quién se pareció el rostro.
- `audit_logs`: autorizaciones, revocaciones, enrolamientos, borrados, purgas,
  cambios de rol, apertura y cierre de sesiones y correcciones de asistencia.

## 3. Pendiente de validación jurídica ⚠️

1. **Texto de la autorización** (`resources/views/biometrics/partials/consent-text.blade.php`).
   Si cambia, subir `BIOMETRIC_CONSENT_VERSION`.
2. **Responsable del tratamiento**: razón social, NIT, dirección y canal de
   atención (`BIOMETRIC_CONTROLLER_NAME`, `BIOMETRIC_CONTACT_EMAIL`). Hoy el
   canal aparece como *pendiente de definir*.
3. **Política de Tratamiento de Información** y **aviso de privacidad** de la
   institución, que deben mencionar este tratamiento.
4. **Registro Nacional de Bases de Datos (RNBD)** ante la SIC, si la institución
   está obligada.
5. **Plazo de retención** de 30 días: es un valor por defecto técnico, no una
   decisión jurídica.
6. **Proporcionalidad**: justificar por qué la biometría es necesaria frente a
   alternativas menos invasivas, sobre todo con menores. Puede requerir una
   evaluación de impacto.
7. **Forma de la autorización de menores**: hoy el personal registra en el
   sistema que el representante autorizó. Definir si además se exige un
   documento firmado (físico o digital) y dónde se archiva.
8. **Encargados**: si el servidor o la base de datos se alojan con un tercero,
   se necesita un contrato de transmisión de datos.
9. **Incidentes de seguridad**: procedimiento de reporte a la SIC.

## 4. Recomendaciones operativas

- Respaldar `APP_KEY` en un gestor de secretos: sin ella los vectores no se descifran.
- Respaldos de la base de datos cifrados y con acceso restringido.
- HTTPS obligatorio en producción (la cámara lo exige fuera de `localhost`).
- Revisar periódicamente `audit_logs` y la tasa de intentos fallidos.
- Capacitar a tutores: el reconocimiento es una ayuda; si hay dudas, registro manual.
