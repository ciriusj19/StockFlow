Set fso = CreateObject("Scripting.FileSystemObject")
Set shell = CreateObject("WScript.Shell")

scriptDir = fso.GetParentFolderName(WScript.ScriptFullName)
batchFile = scriptDir & "\start_stockflow_server.bat"

shell.Run """" & batchFile & """", 0, False
