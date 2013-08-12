#!/bin/sh

echo Start deleting files.
while read filePath; do
  echo deleting file : $filePath
  rm "$filePath" -f
done < upgrade/stages/Cleanup/files.txt

echo Start deleting folders.
while read folderPath; do
  echo deleting folder : $folderPath
  rm "$folderPath" -rf
done < upgrade/stages/Cleanup/folders.txt
