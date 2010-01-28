@echo off
C:
chdir %PROGRAMFILES%\ICW\bin
cygrunsrv.exe -E "openQRM-monitord"
cygrunsrv.exe -R "openQRM-monitord"


