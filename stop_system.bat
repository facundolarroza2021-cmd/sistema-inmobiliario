@echo off
TITLE Deteniendo Sistema...
COLOR 0C
echo ==========================================
echo      DETENIENDO SISTEMA...
echo ==========================================
echo.

:: 1. Nos ubicamos en la carpeta correcta
cd /d "%~dp0"

:: 2. Bajamos los contenedores correctamente
docker compose stop

echo.
echo ==========================================
echo           SISTEMA DETENIDO
echo ==========================================
echo.
echo Puede cerrar esta ventana.
pause