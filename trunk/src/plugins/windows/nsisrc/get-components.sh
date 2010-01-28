#!/bin/bash

COPSSH_DOWNLOAD="http://sourceforge.net/projects/sereds/files/copSSH/3.0.3/Copssh_3.0.3_Installer.zip/download"
WGET_DOWNLOAD="http://users.ugent.be/~bpuype/cgi-bin/fetch.pl?dl=wget/wget.exe"
SLEEP_DOWNLOAD="http://openqrm-ng.net/addons/openqrm-client/4.6/sleep.exe"

if [ ! -f Copssh_3.0.3_Installer.zip ]; then
    echo "-> downloading Copssh 3.0.3 from $COPSSH_DOWNLOAD"
    wget $COPSSH_DOWNLOAD
fi
if [ ! -f wget.exe ]; then
    echo "-> downloading Wget.exe from $WGET_DOWNLOAD"
    wget $WGET_DOWNLOAD
fi
if [ ! -f sleep.exe ]; then
    echo "-> download Sleep.exe from $SLEEP_DOWNLOAD"
    wget $SLEEP_DOWNLOAD
fi


