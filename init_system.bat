@echo off
TITLE Sistema Inmobiliario - Iniciando...
COLOR 0A
echo ==========================================
echo      INICIANDO SISTEMA INMOBILIARIO
echo ==========================================
echo.
echo Verificando sistema...
echo.

:: 1. Nos ubicamos en la carpeta donde está este archivo
cd /d "%~dp0"

:: 2. Levantamos los contenedores en modo silencioso
docker compose up -d

echo.
echo ==========================================
echo      SISTEMA INICIADO CORRECTAMENTE
echo ==========================================
echo.
echo Abriendo el navegador...

:: 3. Esperamos 5 segundos para asegurar que Angular cargue
timeout /t 5 >nul

:: 4. Abrimos Chrome/Edge automáticamente
start http://localhost:4200

echo.
echo Ya puede minimizar o cerrar esta ventana.
echo.
pause