/*

nsDialogs_createIPaddress.nsh
Header file for creating IP address input controls

Usage:
  ${NSD_CreateIPaddress} x y width height text
  ( text is ignored but included keep parity with the other ${NSD_Create*} commands

*/

!ifndef NSDIALOGS_createIPaddress_INCLUDED
	!define NSDIALOGS_createIPaddress_INCLUDED
	!verbose push
	!verbose 3

	!ifndef ICC_INTERNET_CLASSES
		!define ICC_INTERNET_CLASSES 0x00000800
	!endif

	!include LogicLib.nsh
	!include WinMessages.nsh

	!define __NSD_IPaddress_CLASS SysIPAddress32
	!define __NSD_IPaddress_STYLE ${DEFAULT_STYLES}|${WS_TABSTOP}
	!define __NSD_IPaddress_EXSTYLE 0

	!insertmacro __NSD_DefineControl IPaddress

	!macro NSD_InitIPaddress
	    Push $0                 ; $0
	    Push $1                 ; $1 $0
		System::Alloc 400       ; memalloc $1 $0
		Pop $1                  ; $1 $0
		System::Call "*$1(i 8, i ${ICC_INTERNET_CLASSES})"
		System::Call 'comctl32::InitCommonControlsEx(i r1) i .r0'
		System::Free $1
		Pop $1                  ; $0
		Exch $0                 ; 1|0 (true|false)
	!macroend
	!define NSD_InitIPaddress '!insertmacro "NSD_InitIPaddress"'

	!verbose pop
!endif
