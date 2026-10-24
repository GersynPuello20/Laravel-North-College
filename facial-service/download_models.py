"""Descarga los modelos ONNX de OpenCV Zoo y comprueba su SHA-256.

Los modelos no se versionan en git (pesan ~38 MB). Licencias:
- YuNet (detección): MIT.
- SFace (reconocimiento): Apache 2.0.
"""

import hashlib
import pathlib
import sys
import urllib.request

BASE = "https://github.com/opencv/opencv_zoo/raw/main/models"
MODELS = {
    "face_detection_yunet_2023mar.onnx": (
        BASE + "/face_detection_yunet/face_detection_yunet_2023mar.onnx",
        "8f2383e4dd3cfbb4553ea8718107fc0423210dc964f9f4280604804ed2552fa4",
    ),
    "face_recognition_sface_2021dec.onnx": (
        BASE + "/face_recognition_sface/face_recognition_sface_2021dec.onnx",
        "0ba9fbfa01b5270c96627c4ef784da859931e02f04419c829e83484087c34e79",
    ),
}


def sha256(path):
    digest = hashlib.sha256()
    with open(path, "rb") as fh:
        for chunk in iter(lambda: fh.read(1 << 20), b""):
            digest.update(chunk)
    return digest.hexdigest()


def main():
    folder = pathlib.Path(__file__).resolve().parent / "models"
    folder.mkdir(exist_ok=True)
    for name, (url, expected) in MODELS.items():
        target = folder / name
        if target.exists() and sha256(target) == expected:
            print("OK   %s" % name)
            continue
        print("Descargando %s ..." % name)
        tmp = target.with_suffix(".part")
        urllib.request.urlretrieve(url, tmp)
        actual = sha256(tmp)
        if actual != expected:
            tmp.unlink()
            sys.exit("ERROR: el SHA-256 de %s no coincide (%s)." % (name, actual))
        tmp.replace(target)
        print("OK   %s" % name)


if __name__ == "__main__":
    main()
