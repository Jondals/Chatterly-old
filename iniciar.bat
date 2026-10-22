@echo off
cd /d "%~dp0"
set PHP=php
where php >nul 2>nul || set PHP=%USERPROFILE%\.config\herd-lite\bin\php.exe
set PHP_CLI_SERVER_WORKERS=4
start "" http://localhost:8000/
"%PHP%" -S localhost:8000 -t .
