;openQRM Client Windows installer
;by Matt Rechenburg (matt at openqrm.com)

;--------------------------------
;Include Modern UI

!include "MUI2.nsh"
!include 'nsdialogs.nsh'
!include 'nsdialogs_createIPaddress.nsh'


!define OQPROGRAMNAME "openQRM-Client"
!define OQPROGRAMVERSION "4.6.1"
!define OQPROGRAMLICENSE "license.txt"
!define OQSERVERIPCONF "openqrm-server-ip.conf"
!define OQRESOURCECONF "resource-id.conf"
!define OQRESOURCEPARAMETER "resource-parameter.conf"
!define OQCOMPUTERNAME "computer-name.conf"
!define OQRETRIES "1"
!define OQTIMEOUT "5"


; Copssh version 3.0.3
!define OQCOPSSH "Copssh_3.0.3_Installer.exe"
; wget statically linked from http://users.ugent.be/~bpuype/wget/
!define OQWGET "wget.exe"



;--------------------------------
;General

  ;Name and file
  Name ${OQPROGRAMNAME}-${OQPROGRAMVERSION}
  OutFile "${OQPROGRAMNAME}-${OQPROGRAMVERSION}-setup.exe"

  ;Default installation folder
  InstallDir "$PROGRAMFILES\${OQPROGRAMNAME}"

  ;Get installation folder from registry if available
  InstallDirRegKey HKCU "Software\${OQPROGRAMNAME}" ""

  ;Request application privileges for Windows Vista
  RequestExecutionLevel admin

;--------------------------------
;Interface Settings

  !define MUI_ABORTWARNING

;--------------------------------
;Pages

  !insertmacro MUI_PAGE_WELCOME

  ;Page Custom CheckIfInstalledAlready

  !insertmacro MUI_PAGE_LICENSE "${OQPROGRAMLICENSE}"

  Page Custom InstallNotice

  !insertmacro MUI_PAGE_DIRECTORY
  !insertmacro MUI_PAGE_INSTFILES

  Page Custom GetComputerName
  Page Custom OpenQrmIpDialog GetOpenQrmIp
  Page Custom GetOpenQrmPublicKey
  Page Custom AskForResId GetResourceParameter
  Page Custom RegisterMonitoring

  !insertmacro MUI_PAGE_FINISH

  !insertmacro MUI_UNPAGE_WELCOME
  !insertmacro MUI_UNPAGE_CONFIRM
  !insertmacro MUI_UNPAGE_INSTFILES
  !insertmacro MUI_UNPAGE_FINISH

;--------------------------------
;Languages

  !insertmacro MUI_LANGUAGE "English"

;--------------------------------
;Function Sections

; global vars
var ip
var computername
var resid
var Label
var Dialog
var retcode




Function InstallNotice
	MessageBox MB_OK "--------------------------------------------------- PLEASE NOTICE ---------------------------------------------------$\nTo install ${OQPROGRAMNAME} this system needs to be already integrated into openQRM by network-booting it via PXE.$\nIf you have not done it yet please Simply restart this system, set its boot-sequence to first boot from the network.$\nThe system will now seamlessly integrated into openQRM.$\n$\nPlease do this steps in advance and keep in mind the Systems openQRM Resource-ID!$\n$\nAlso please keep the default installation-location. Many thanks and Enjoy !$\n"
FunctionEnd



; check if we are already installed
Function CheckIfInstalledAlready

	IfFileExists "$INSTDIR\${OQSERVERIPCONF}" installed
		return

	installed:
		MessageBox MB_OK "${OQPROGRAMNAME} is already installed ! Please uninstall first."
		Quit

FunctionEnd



; writes the computer name to a config file
Function GetComputerName
	ReadRegStr $0 HKLM "System\CurrentControlSet\Control\ComputerName\ActiveComputerName" "ComputerName"
	StrCmp $0 "" win9x
	StrCpy $1 $0 4 3
	Goto done
win9x:
	ReadRegStr $0 HKLM "System\CurrentControlSet\Control\ComputerName\ComputerName" "ComputerName"
	StrCpy $1 $0 4 3
done:
	FileOpen $6 "$INSTDIR\${OQCOMPUTERNAME}" w
	FileWrite $6 "$0"
	FileClose $6
	;MessageBox MB_OK "Your ComputerName : $0" 
	return
FunctionEnd




; dialog to provide the openQRM ip address 
Function OpenQrmIpDialog
	nsDialogs::Create 1018
	Pop $R0
 
	${If} $R0 == error
		Abort
	${EndIf}
 
	${NSD_InitIPaddress}
	Pop $0
	IntCmp $0 0 0 +3 +3
	    MessageBox MB_OK "Something went wrong while initializing the IPaddress control"
	    Abort

	${NSD_CreateIPaddress} 5% 90% 30% 12u ""
	Pop $ip

	MessageBox MB_OK "Please provide the ip-address of the openQRM Server."
 	nsDialogs::Show

FunctionEnd


; writes openQRM server ip to config file
Function GetOpenQrmIp
	${NSD_GetText} $ip $0
	;MessageBox MB_OK "You typed:$\n$\n$0"
	FileOpen $9 "$INSTDIR\${OQSERVERIPCONF}" w
	FileWrite $9 "$0"
	FileClose $9
FunctionEnd





; aks for the systems resource id
Function AskForResId
	nsDialogs::Create 1018
	Pop $Dialog

	${If} $Dialog == error
		Abort
	${EndIf}

	${NSD_CreateLabel} 0 0 100% 12u "Please enter the openQRM resource ID of this system in the field below :"
	Pop $Label

	${NSD_CreateText} 0 13u 10% 10% ""
	Pop $resid

	nsDialogs::Show

FunctionEnd


; this function receives the resource paramaters
Function GetResourceParameter

	${NSD_GetText} $resid $0
	; MessageBox MB_OK "You typed:$\n$\n$0"
	FileOpen $9 "$INSTDIR\${OQRESOURCECONF}" w
	FileWrite $9 "$0"
	FileClose $9

	; read openqrm ip from file
	FileOpen $2 "$INSTDIR\${OQSERVERIPCONF}" r
	FileRead $2 $3
	FileClose $2

	MessageBox MB_OK "Getting resource parameter from openQRM Server at : $3"

	!define GETRESPARAMCMD "$INSTDIR\wget.exe -t ${OQRETRIES} -w ${OQTIMEOUT} -O $INSTDIR\resource-parameter.conf http://$3/openqrm/action/resource-monitor.php?resource_command=get_parameter^&resource_id=$0"
	; write to bat file to execute, much safer
	FileOpen $9 "$INSTDIR\resource-parameter.bat" w
	FileWrite $9 "${GETRESPARAMCMD}"
	FileClose $9
	nsExec::ExecToStack '"$INSTDIR\resource-parameter.bat"'
	Pop $retcode ; contains the error code
	Pop $0 ; contains the cmd output
	strCmp $retcode "0" 0 badexit1
		return

	; provide debug infos in case it fails
	badexit1:
		MessageBox MB_OK "Bad exit code while getting the resource parameter from openQRM Server at $3 : $4"
		MessageBox MB_OK "${GETRESPARAMCMD}"
		MessageBox MB_OK "Returned : $0"
;		Quit


FunctionEnd





; gets the openQRM servers public key, adds it to root/.ssh/authorized_keys
Function GetOpenQrmPublicKey

	; read openqrm ip from file
	FileOpen $2 "$INSTDIR\${OQSERVERIPCONF}" r
	FileRead $2 $3
	FileClose $2

	MessageBox MB_OK "Getting public key of openQRM Server at : $3"

	; run wget
	!define WGETCMD "$INSTDIR\wget.exe -t ${OQRETRIES} -w ${OQTIMEOUT} -O $PROGRAMFILES\ICW\home\root\.ssh\authorized_keys_add http://$3/openqrm/boot-service/openqrm-server-public-rsa-key"

	FileOpen $9 "$INSTDIR\public-key.bat" w
	FileWrite $9 "${WGETCMD}"
	FileClose $9

	nsExec::ExecToStack '"$INSTDIR\public-key.bat"'
	Pop $retcode ; contains the error code
	Pop $0 ; contains the cmd output
	strCmp $retcode "0" 0 badexit
		; add the key to authorized_keys of user root
		FileOpen $2 "$PROGRAMFILES\ICW\home\root\.ssh\authorized_keys_add" r
		FileRead $2 $3
		FileClose $2
		FileOpen $2 "$PROGRAMFILES\ICW\home\root\.ssh\authorized_keys" a
		FileWrite $2 "$\n$3$\n"
		FileClose $2
		return

	; provide debug infos in case it fails
	badexit:
		MessageBox MB_OK "Bad exit code while getting the public key from openQRM Server at $3 : $4"
		MessageBox MB_OK "${WGETCMD}"
		MessageBox MB_OK "Returned : $0"
		Quit
FunctionEnd




; register and start openQRM monitord
Function RegisterMonitoring
  ; add monitoring service
  MessageBox MB_OK "Adding openQRM Monitoring Service"
  ; stop + remove monitoring service
  ExecWait '"$INSTDIR\register-openqrm-monitord-service.bat"'

FunctionEnd


;--------------------------------
;Installer Sections


; check if user is admin
section
    # call userInfo plugin to get user info.  The plugin puts the result in the stack
    userInfo::getAccountType
    # pop the result from the stack into $0
    pop $0
    # compare the result with the string "Admin" to see if the user is admin.
    # If match, jump 3 lines down.
    strCmp $0 "Admin" +3
    # if there is not a match, print message and return
    messageBox MB_OK "You must be in the Administrator Group to install openQRM-Client !"
    return
# default section end
sectionEnd



; install wget
Section "openQRM Monitoring subsystem" SecWget
  SetOutPath "$INSTDIR"

  ; install license file
  File ${OQPROGRAMLICENSE}
  ;install wget
  File ${OQWGET}
  ; install other utils
  File "sleep.exe"
  File "openqrm-monitord.win"
  File "register-openqrm-monitord-service.bat"
  File "unregister-openqrm-monitord-service.bat"


  ;Store installation folder
  WriteRegStr HKCU "Software\${OQPROGRAMNAME}" "" $INSTDIR

  ;Create uninstaller
  WriteUninstaller "$INSTDIR\Uninstall.exe"

  ; write uninstall infos into the registry
  WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\openQRM-Client" \
                 "DisplayName" "openQRM-Client"
  WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\openQRM-Client" \
                 "UninstallString" "$\"$INSTDIR\uninstall.exe$\""

SectionEnd



; install copssh
Section "openQRM Exec subsystem" SecCopssh
   SetOutPath "$INSTDIR"
   File ${OQCOPSSH}

   MessageBox MB_OK "Copssh installation. Please setup with the defaults"
   ExecWait '"$INSTDIR\${OQCOPSSH}"' $0

   MessageBox MB_OK "Copssh setup. Please activate user root"
   Execwait '"$PROGRAMFILES\ICW\Bin\UserActivationWizard.exe"'


SectionEnd


;--------------------------------
;Descriptions

  ;Language strings
  LangString DESC_SecWget ${LANG_ENGLISH} "openQRMs monitoring subsystem via Wget."
  LangString DESC_SecCopssh ${LANG_ENGLISH} "openQRMs remote execution via Copssh."

  ;Assign language strings to sections
  !insertmacro MUI_FUNCTION_DESCRIPTION_BEGIN
    !insertmacro MUI_DESCRIPTION_TEXT ${SecWget} $(DESC_SecWget)
    !insertmacro MUI_DESCRIPTION_TEXT ${SecCopssh} $(DESC_SecCopssh)
  !insertmacro MUI_FUNCTION_DESCRIPTION_END

;--------------------------------
;Uninstaller Section

Section "Uninstall"

  ; stop + remove monitoring service
  ExecWait '"$INSTDIR\unregister-openqrm-monitord-service.bat"'

  ; copssh uninstall first
  MessageBox MB_OK "Copssh Uninstallation"
  Execwait '"$PROGRAMFILES\ICW\uninstall_Copssh.exe" _?=$INSTDIR'
  Execwait '"$PROGRAMFILES\ICW\uninstall_ICW_OpenSSHServer.exe"'
  Execwait '"$PROGRAMFILES\ICW\uninstall_ICW_Base.exe"'


  ;file uninstall
  Delete "$INSTDIR\Uninstall.exe"
  Delete "$INSTDIR\${OQCOPSSH}"
  Delete "$INSTDIR\${OQWGET}"
  Delete "$INSTDIR\${OQPROGRAMLICENSE}"
  Delete "$INSTDIR\${OQPROGRAMLICENSE}"
  Delete "$INSTDIR\${OQSERVERIPCONF}"
  Delete "$INSTDIR\${OQCOMPUTERNAME}"
  Delete "$INSTDIR\${OQRESOURCECONF}"
  Delete "$INSTDIR\${OQRESOURCEPARAMETER}"
  Delete "$INSTDIR\public-key.bat"
  Delete "$INSTDIR\resource-parameter.bat"
  Delete "$INSTDIR\sleep.exe"
  Delete "$INSTDIR\openqrm-monitord.win"
  Delete "$INSTDIR\unregister-openqrm-monitord-service.bat"
  Delete "$INSTDIR\register-openqrm-monitord-service.bat"

  Delete "$INSTDIR\myhome.lnk"
  Delete "$INSTDIR\myhome"

  RMDir "$INSTDIR"

  ; remove registry entries
  DeleteRegKey /ifempty HKCU "Software\${OQPROGRAMNAME}"
  DeleteRegKey HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\openQRM-Client"

SectionEnd
