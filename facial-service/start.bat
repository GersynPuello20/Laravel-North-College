@echo off
rem Arranca el motor biometrico local (http://127.0.0.1:8765).
rem Lee FACE_SERVICE_TOKEN del .env de Laravel para que ambos usen el mismo.
setlocal
cd /d "%~dp0"

where py >nul 2>nul && (set "PY=py -3") || (set "PY=python")

if not exist ".venv\Scripts\python.exe" (
    echo Creando entorno virtual...
    %PY% -m venv .venv || goto :error
    ".venv\Scripts\python.exe" -m pip install --disable-pip-version-check -r requirements.txt || goto :error
)

".venv\Scripts\python.exe" download_models.py || goto :error

if "%FACE_SERVICE_TOKEN%"=="" (
    for /f "usebackq tokens=1,* delims==" %%A in ("..\.env") do (
        if "%%A"=="FACE_SERVICE_TOKEN" set "FACE_SERVICE_TOKEN=%%~B"
    )
)
if "%FACE_SERVICE_TOKEN%"=="" (
    echo ERROR: falta FACE_SERVICE_TOKEN en ..\.env
    goto :error
)

".venv\Scripts\python.exe" server.py
goto :eof

:error
echo No se pudo iniciar el motor biometrico.
exit /b 1
