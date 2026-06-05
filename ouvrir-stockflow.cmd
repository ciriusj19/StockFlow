@echo off
setlocal EnableExtensions

set "ROOT_DIR=%~dp0"
pushd "%ROOT_DIR%" >nul 2>nul
if errorlevel 1 (
    echo [ERREUR] Impossible d'ouvrir le dossier StockFlow.
    pause
    exit /b 1
)

set "PHP_BIN=C:\xampp\php\php.exe"
if not exist "%PHP_BIN%" set "PHP_BIN=php"

set "STOCKFLOW_HOST=127.0.0.1"
if not defined STOCKFLOW_PORT set "STOCKFLOW_PORT=8000"
set "STOCKFLOW_URL=http://%STOCKFLOW_HOST%:%STOCKFLOW_PORT%/login"

echo StockFlow
echo URL : %STOCKFLOW_URL%
echo Identifiants : admin@stockflow.local / password
echo.

start "StockFlow Server" cmd /k "cd /d ""%ROOT_DIR%"" && ""%PHP_BIN%"" -S %STOCKFLOW_HOST%:%STOCKFLOW_PORT% -t public scripts\laravel_dev_router.php"
timeout /t 2 /nobreak >nul
start "" "%STOCKFLOW_URL%"

popd >nul
exit /b 0
