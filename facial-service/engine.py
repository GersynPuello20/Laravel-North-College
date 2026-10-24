"""Motor biométrico de North College (OpenCV YuNet + SFace).

Recibe fotogramas en memoria, nunca los escribe a disco y devuelve solo
métricas de calidad, el resultado de la prueba de presencia y el vector
biométrico. La comparación contra estudiantes la hace Laravel.
"""

from __future__ import annotations

import base64
import os
import threading
from dataclasses import dataclass, field

import cv2
import numpy as np

MODELS_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "models")
DETECTOR_MODEL = "face_detection_yunet_2023mar.onnx"
RECOGNIZER_MODEL = "face_recognition_sface_2021dec.onnx"
ENGINE_NAME = "opencv-yunet-2023mar+sface-2021dec"


def _env_float(name: str, default: float) -> float:
    try:
        return float(os.environ.get(name, default))
    except ValueError:
        return default


@dataclass
class Settings:
    # Puntaje mínimo de YuNet (0.9 es el valor de los ejemplos de OpenCV).
    min_detection_score: float = _env_float("FACE_MIN_DETECTION_SCORE", 0.9)
    # Ancho mínimo del rostro en píxeles para considerar la captura útil.
    min_face_width: float = _env_float("FACE_MIN_FACE_WIDTH", 80)
    # Brillo medio aceptable del recorte del rostro (0-255).
    min_brightness: float = _env_float("FACE_MIN_BRIGHTNESS", 40)
    max_brightness: float = _env_float("FACE_MAX_BRIGHTNESS", 220)
    # Varianza del laplaciano: por debajo, la imagen está borrosa.
    min_sharpness: float = _env_float("FACE_MIN_SHARPNESS", 30)
    # Prueba de presencia: giro de cabeza medido con los puntos faciales.
    frontal_max_yaw: float = _env_float("FACE_FRONTAL_MAX_YAW", 0.18)
    turned_min_yaw: float = _env_float("FACE_TURNED_MIN_YAW", 0.30)
    # Similitud mínima entre fotogramas de la misma captura (misma persona).
    consistency_threshold: float = _env_float("FACE_CONSISTENCY_THRESHOLD", 0.30)
    max_frames: int = int(_env_float("FACE_MAX_FRAMES", 12))


class EngineError(Exception):
    def __init__(self, code: str, message: str):
        super().__init__(message)
        self.code = code
        self.message = message


@dataclass
class FrameAnalysis:
    index: int
    face_count: int = 0
    detection_score: float = 0.0
    face_width: float = 0.0
    brightness: float = 0.0
    sharpness: float = 0.0
    yaw: float | None = None
    issues: list[str] = field(default_factory=list)
    feature: np.ndarray | None = None

    @property
    def usable(self) -> bool:
        return self.face_count == 1 and not self.issues

    def public(self) -> dict:
        return {
            "index": self.index,
            "face_count": self.face_count,
            "detection_score": round(self.detection_score, 3),
            "face_width": round(self.face_width, 1),
            "brightness": round(self.brightness, 1),
            "sharpness": round(self.sharpness, 1),
            "yaw": None if self.yaw is None else round(self.yaw, 3),
            "issues": self.issues,
        }


def decode_image(data: str) -> np.ndarray:
    if "," in data and data.strip().startswith("data:"):
        data = data.split(",", 1)[1]
    try:
        raw = base64.b64decode(data, validate=True)
    except (ValueError, TypeError):
        raise EngineError("invalid_image", "La imagen no está codificada correctamente.")
    image = cv2.imdecode(np.frombuffer(raw, dtype=np.uint8), cv2.IMREAD_COLOR)
    if image is None:
        raise EngineError("invalid_image", "No se pudo leer la imagen.")
    return image


def yaw_proxy(face: np.ndarray) -> float:
    """Desplazamiento horizontal de la nariz respecto al centro de los ojos,
    normalizado por la distancia entre ojos, en coordenadas de la imagen sin
    espejo. Vale ~0 de frente, es positivo cuando la persona gira hacia su
    izquierda y negativo hacia su derecha.

    Limitación medida: en teoría una foto plana inclinada no cambia esta
    proporción, pero los puntos de YuNet son aproximados y una foto de prueba
    comprimida al 80 % dio 0.26. Inclinar una foto puede superar el reto."""
    right_eye = face[4:6]
    left_eye = face[6:8]
    nose = face[8:10]
    eye_distance = float(np.linalg.norm(left_eye - right_eye))
    if eye_distance < 1e-6:
        return 0.0
    eyes_center_x = (right_eye[0] + left_eye[0]) / 2.0
    return float((nose[0] - eyes_center_x) / eye_distance)


class FaceEngine:
    def __init__(self, settings: Settings | None = None):
        self.settings = settings or Settings()
        self._lock = threading.Lock()
        self._detector = cv2.FaceDetectorYN.create(
            os.path.join(MODELS_DIR, DETECTOR_MODEL), "", (320, 320),
            self.settings.min_detection_score, 0.3, 5000,
        )
        self._recognizer = cv2.FaceRecognizerSF.create(
            os.path.join(MODELS_DIR, RECOGNIZER_MODEL), ""
        )

    def info(self) -> dict:
        return {
            "engine": ENGINE_NAME,
            "opencv": cv2.__version__,
            "embedding_dim": 128,
            "settings": self.settings.__dict__,
        }

    def analyze_frame(self, index: int, image: np.ndarray, with_feature: bool) -> FrameAnalysis:
        s = self.settings
        result = FrameAnalysis(index=index)
        height, width = image.shape[:2]

        with self._lock:
            self._detector.setInputSize((width, height))
            _, faces = self._detector.detect(image)

            if faces is None or len(faces) == 0:
                result.issues.append("no_face")
                return result

            result.face_count = len(faces)
            if len(faces) > 1:
                result.issues.append("multiple_faces")
                return result

            face = faces[0]
            result.detection_score = float(face[-1])
            result.face_width = float(face[2])
            result.yaw = yaw_proxy(face)

            x, y, w, h = (int(max(v, 0)) for v in face[:4])
            crop = image[y:y + h, x:x + w]
            if crop.size:
                gray = cv2.cvtColor(crop, cv2.COLOR_BGR2GRAY)
                result.brightness = float(gray.mean())
                result.sharpness = float(cv2.Laplacian(gray, cv2.CV_64F).var())

            if result.detection_score < s.min_detection_score:
                result.issues.append("low_detection_score")
            if result.face_width < s.min_face_width:
                result.issues.append("face_too_small")
            if result.brightness < s.min_brightness:
                result.issues.append("too_dark")
            if result.brightness > s.max_brightness:
                result.issues.append("too_bright")
            if result.sharpness < s.min_sharpness:
                result.issues.append("blurry")

            if with_feature and not result.issues:
                aligned = self._recognizer.alignCrop(image, face)
                feature = self._recognizer.feature(aligned).flatten().astype(np.float64)
                norm = np.linalg.norm(feature)
                result.feature = feature / norm if norm > 0 else feature

        return result

    def detect(self, image_b64: str) -> dict:
        frame = self.analyze_frame(0, decode_image(image_b64), with_feature=False)
        return {"ok": frame.usable, "frame": frame.public()}

    def embed(self, frames_b64: list[str], require_liveness: bool, direction: str | None = None) -> dict:
        s = self.settings
        if not frames_b64:
            raise EngineError("no_frames", "No se recibieron fotogramas.")
        if len(frames_b64) > s.max_frames:
            raise EngineError("too_many_frames", "Se recibieron demasiados fotogramas.")

        frames = [self.analyze_frame(i, decode_image(b), with_feature=True) for i, b in enumerate(frames_b64)]
        public = [f.public() for f in frames]
        usable = [f for f in frames if f.usable]

        if any("multiple_faces" in f.issues for f in frames):
            return self._failure("multiple_faces", "Hay más de un rostro en la imagen.", public)
        if not usable:
            reasons = sorted({issue for f in frames for issue in f.issues})
            code = reasons[0] if len(reasons) == 1 else "low_quality"
            return self._failure(code, "Ningún fotograma tiene la calidad suficiente.", public, reasons)

        frontal = [f for f in usable if abs(f.yaw) <= s.frontal_max_yaw]
        if direction == "left":
            turned = [f for f in usable if f.yaw >= s.turned_min_yaw]
        elif direction == "right":
            turned = [f for f in usable if f.yaw <= -s.turned_min_yaw]
        else:
            turned = [f for f in usable if abs(f.yaw) >= s.turned_min_yaw]

        if not frontal:
            return self._failure("no_frontal_face", "Mira de frente a la cámara.", public)

        reference = max(frontal, key=lambda f: f.detection_score)

        liveness = {"required": require_liveness, "passed": False, "method": "head_turn_challenge", "direction": direction}
        if require_liveness:
            if not turned:
                liveness["reason"] = "no_head_turn"
                return self._failure("liveness_failed", "No se detectó el giro de cabeza solicitado.", public, liveness=liveness)
            # Todos los fotogramas útiles deben pertenecer a la misma persona.
            for f in usable:
                score = float(np.dot(reference.feature, f.feature))
                if score < s.consistency_threshold:
                    liveness["reason"] = "inconsistent_identity"
                    return self._failure("liveness_failed", "Los fotogramas no parecen de la misma persona.", public, liveness=liveness)
            liveness["passed"] = True
            liveness["max_yaw"] = round(max(abs(f.yaw) for f in turned), 3)

        # El vector final promedia los fotogramas frontales (más estables).
        stacked = np.mean([f.feature for f in frontal], axis=0)
        embedding = stacked / np.linalg.norm(stacked)

        quality = float(np.mean([f.detection_score for f in frontal]))
        return {
            "ok": True,
            "engine": ENGINE_NAME,
            "embedding": [round(float(v), 7) for v in embedding],
            "quality": round(quality, 3),
            "frontal_frames": len(frontal),
            "liveness": liveness,
            "frames": public,
        }

    @staticmethod
    def _failure(code: str, message: str, frames: list[dict], reasons: list[str] | None = None, liveness: dict | None = None) -> dict:
        payload = {"ok": False, "error": code, "message": message, "frames": frames}
        if reasons:
            payload["reasons"] = reasons
        if liveness:
            payload["liveness"] = liveness
        return payload
