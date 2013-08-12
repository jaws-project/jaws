echo Start deleting files.
@echo off
setlocal EnableDelayedExpansion

for /F "delims=" %%i in (upgrade\stages\Cleanup\files.txt) do (
set "fileName=%%i"
set "filePath=!fileName:/=\!"

	@echo on
	echo deleting file : !filePath!
	del "!filePath!" /F 
	@echo off
)

@echo on
echo Start deleting folders.
@echo off


for /F "delims=" %%i in (upgrade\stages\Cleanup\folders.txt) do (
set "folderName=%%i"
set "folderPath=!folderName:/=\!"

	@echo on
	echo deleting folder : !folderPath!

	DEL /F /Q /S "!folderPath!\*"
	for /D %%c in ("!folderPath!\*") do RD /S /Q "%%c"
	RD !folderPath!
	
	@echo off
)