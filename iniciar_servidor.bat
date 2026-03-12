@echo off
chcp 65001 > nul
echo Iniciando Servidor da Barbearia Elite (UTF-8)...
"..\php-bin\php.exe" -d max_execution_time=120 -S localhost:8000 local_serve.php
pause

