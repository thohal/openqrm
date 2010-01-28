@echo off
C:
chdir %PROGRAMFILES%\ICW\bin
cygrunsrv.exe -I "openQRM-monitord" -t auto -O -p "/cygdrive/c/Programme/openQRM-Client/openqrm-monitord.win"
cygrunsrv.exe -S "openQRM-monitord"


