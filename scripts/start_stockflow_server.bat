@echo off
setlocal EnableExtensions

set "ROOT_DIR=%~dp0.."
pushd "%ROOT_DIR%" >nul 2>nul
if errorlevel 1 (
    echo [ERREUR] Impossible d'ouvrir le dossier du projet.
    pause
    exit /b 1
)

echo.
echo StockFlow - demarrage local
echo Dossier : %CD%
echo.

set "PHP_BIN="
where php >nul 2>nul
if not errorlevel 1 set "PHP_BIN=php"
if not defined PHP_BIN if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"

if not defined PHP_BIN (
    echo [ERREUR] PHP est introuvable.
    echo Installe XAMPP ou ajoute PHP dans la variable PATH.
    echo Chemin accepte automatiquement : C:\xampp\php\php.exe
    echo.
    pause
    popd >nul
    exit /b 1
)

if not exist "artisan" (
    echo [ERREUR] Le fichier artisan est introuvable.
    echo Lance ce script depuis le dossier StockFlow ou utilise lancer-stockflow.cmd.
    echo.
    pause
    popd >nul
    exit /b 1
)

if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo .env cree depuis .env.example.
    ) else (
        echo [ERREUR] .env est absent et .env.example est introuvable.
        echo.
        pause
        popd >nul
        exit /b 1
    )
)

findstr /B /C:"APP_KEY=base64:" ".env" >nul 2>nul
if errorlevel 1 (
    echo Generation de la cle Laravel...
    "%PHP_BIN%" artisan key:generate
    if errorlevel 1 (
        echo [ERREUR] La generation de la cle Laravel a echoue.
        echo.
        pause
        popd >nul
        exit /b 1
    )
)

if not exist "vendor\autoload.php" (
    echo [ERREUR] Les dependances PHP sont absentes.
    echo Tape d'abord : composer install
    echo.
    pause
    popd >nul
    exit /b 1
)

set "RUN_MIGRATIONS=0"
set "RUN_SEED=0"
if /I "%STOCKFLOW_MIGRATE%"=="1" set "RUN_MIGRATIONS=1"

if not exist "database" mkdir "database" >nul 2>nul
if not exist "database\database.sqlite" (
    type nul > "database\database.sqlite"
    echo Base SQLite creee : database\database.sqlite
    set "RUN_MIGRATIONS=1"
    set "RUN_SEED=1"
)

if not exist "public\build\manifest.json" (
    echo Assets Vite absents. Compilation en cours...
    where npm.cmd >nul 2>nul
    if errorlevel 1 (
        echo [ERREUR] npm.cmd est introuvable. Installe Node.js puis tape : npm.cmd run build
        echo.
        pause
        popd >nul
        exit /b 1
    )

    call npm.cmd run build
    if errorlevel 1 (
        echo [ERREUR] La compilation des assets a echoue.
        echo.
        pause
        popd >nul
        exit /b 1
    )
)

if "%RUN_MIGRATIONS%"=="1" (
    echo Application des migrations...
    "%PHP_BIN%" artisan migrate --force
    if errorlevel 1 (
        echo [ERREUR] Les migrations Laravel ont echoue.
        echo.
        pause
        popd >nul
        exit /b 1
    )
    if "%RUN_SEED%"=="1" (
        echo Creation des donnees de demo...
        "%PHP_BIN%" artisan db:seed --force
        if errorlevel 1 (
            echo [ERREUR] La creation des donnees de demo a echoue.
            echo.
            pause
            popd >nul
            exit /b 1
        )
    )
) else (
    echo Base SQLite presente. Migrations ignorees au demarrage.
    echo Pour forcer les migrations : set STOCKFLOW_MIGRATE=1
)

if not defined STOCKFLOW_HOST set "STOCKFLOW_HOST=127.0.0.1"
if not defined STOCKFLOW_PORT set "STOCKFLOW_PORT=8000"
set "STOCKFLOW_URL=http://%STOCKFLOW_HOST%:%STOCKFLOW_PORT%/login"

powershell -NoProfile -ExecutionPolicy Bypass -Command "try { $r = Invoke-WebRequest -UseBasicParsing -Uri '%STOCKFLOW_URL%' -TimeoutSec 2; if ($r.StatusCode -ge 200) { exit 0 } } catch { exit 1 }" >nul 2>nul
if not errorlevel 1 (
    echo StockFlow semble deja lance.
    echo URL : %STOCKFLOW_URL%
    echo.
    popd >nul
    exit /b 0
)

echo.
echo Serveur local : %STOCKFLOW_URL%
echo Identifiants : admin@stockflow.local / password
echo Pour arreter le serveur : Ctrl+C
echo.

"%PHP_BIN%" -S %STOCKFLOW_HOST%:%STOCKFLOW_PORT% -t public scripts\laravel_dev_router.php
set "EXIT_CODE=%ERRORLEVEL%"

echo.
echo Serveur arrete avec le code %EXIT_CODE%.
if not "%EXIT_CODE%"=="0" (
    echo Si le port %STOCKFLOW_PORT% est deja utilise, ferme l'autre serveur ou lance :
    echo set STOCKFLOW_PORT=8001
    echo scripts\start_stockflow_server.bat
    echo.
    pause
)

popd >nul
exit /b %EXIT_CODE%
