@echo off
setlocal enabledelayedexpansion

:: URL and payload
set URL="https://alpha.ke5.us/xhr/record?record_name=ticker&allow_cache=true"
set PAYLOAD={"r":"ticker"}

:loop

:: Loop to perform 20 requests per second
for /l %%i in (1,1,20) do (
    curl -X GET %URL% -H "Content-Type: application/json" -d "%PAYLOAD%" >nul 2>&1
)

goto loop

:: End script
endlocal
