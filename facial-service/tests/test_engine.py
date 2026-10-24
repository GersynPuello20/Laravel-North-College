"""Pruebas del motor biométrico.

Las pruebas con rostros reales necesitan imágenes que no se guardan en el
repositorio. Para ejecutarlas, define FACE_TEST_IMAGES con una carpeta que
contenga lena.jpg y messi5.jpg (muestras públicas de OpenCV:
https://github.com/opencv/opencv/tree/4.x/samples/data).

    .venv\\Scripts\\python.exe -m unittest discover -s tests
"""

import base64
import os
import sys
import unittest

import cv2
import numpy as np

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from engine import EngineError, FaceEngine, Settings  # noqa: E402

IMAGES = os.environ.get("FACE_TEST_IMAGES", "")


def encode(image: np.ndarray) -> str:
    ok, buffer = cv2.imencode(".jpg", image)
    assert ok
    return base64.b64encode(buffer.tobytes()).decode()


def load(name: str) -> np.ndarray:
    return cv2.imread(os.path.join(IMAGES, name))


class EngineWithoutFacesTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.engine = FaceEngine()

    def test_blank_image_has_no_face(self):
        blank = np.full((480, 640, 3), 128, dtype=np.uint8)
        result = self.engine.embed([encode(blank)], require_liveness=False)
        self.assertFalse(result["ok"])
        self.assertEqual(result["error"], "no_face")
        self.assertNotIn("embedding", result)

    def test_invalid_base64_is_rejected(self):
        with self.assertRaises(EngineError) as ctx:
            self.engine.detect("esto-no-es-una-imagen")
        self.assertEqual(ctx.exception.code, "invalid_image")

    def test_empty_frames_are_rejected(self):
        with self.assertRaises(EngineError):
            self.engine.embed([], require_liveness=True)


@unittest.skipUnless(IMAGES and os.path.exists(os.path.join(IMAGES, "lena.jpg")), "FACE_TEST_IMAGES no definido")
class EngineWithFacesTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        # Lena está algo girada (yaw ~0.17); se amplía el margen frontal para usarla.
        cls.engine = FaceEngine(Settings(frontal_max_yaw=0.2))
        cls.lena = load("lena.jpg")
        cls.messi = load("messi5.jpg")

    def test_embedding_is_normalized_and_128_dims(self):
        result = self.engine.embed([encode(self.lena)], require_liveness=False)
        self.assertTrue(result["ok"], result)
        vector = np.array(result["embedding"])
        self.assertEqual(vector.shape, (128,))
        self.assertAlmostEqual(float(np.linalg.norm(vector)), 1.0, places=4)

    def test_same_person_scores_above_reference_threshold(self):
        a = np.array(self.engine.embed([encode(self.lena)], require_liveness=False)["embedding"])
        b = np.array(self.engine.embed([encode(cv2.flip(self.lena, 1))], require_liveness=False)["embedding"])
        self.assertGreater(float(np.dot(a, b)), 0.363)

    def test_different_people_score_below_reference_threshold(self):
        engine = FaceEngine(Settings(frontal_max_yaw=0.3, min_face_width=20))
        a = np.array(engine.embed([encode(self.lena)], require_liveness=False)["embedding"])
        b = np.array(engine.embed([encode(self.messi)], require_liveness=False)["embedding"])
        self.assertLess(float(np.dot(a, b)), 0.363)

    def test_static_photo_fails_liveness(self):
        frames = [encode(self.lena)] * 4
        result = self.engine.embed(frames, require_liveness=True, direction="left")
        self.assertFalse(result["ok"])
        self.assertEqual(result["error"], "liveness_failed")

    def test_two_faces_side_by_side_are_rejected(self):
        both = np.hstack([self.lena, self.lena])
        result = self.engine.embed([encode(both)], require_liveness=False)
        self.assertFalse(result["ok"])
        self.assertEqual(result["error"], "multiple_faces")

    def test_small_face_is_reported(self):
        result = self.engine.detect(encode(self.messi))
        self.assertFalse(result["ok"])
        self.assertIn("face_too_small", result["frame"]["issues"])


if __name__ == "__main__":
    unittest.main()
