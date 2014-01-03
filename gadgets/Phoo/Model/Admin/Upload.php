<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Model_Admin_Upload extends Jaws_Gadget_Model
{
    /**
     * Check if input (an array of $_FILES) are .tar or .zip files, if they
     * are then these get unpacked and returns an managed as $_FILES (returning
     * an array with the same structure $_FILES uses and move pics to /tmp)
     *
     * @access  public
     * @param   array   $files   $_FILES
     * @return  array   $_FILES format
     */
    function UnpackFiles($files)
    {
        if (!is_array($files)) {
            return array();
        }

        $cleanFiles = array();
        $tmpDir     = sys_get_temp_dir();
        $counter    = 1;
        require_once PEAR_PATH. 'File/Archive.php';
        foreach($files as $key => $file) {
            if (empty($file['tmp_name'])) {
                continue;
            }

            $ext = strrchr($file['name'], '.');
            switch ($ext) {
                case '.gz':
                    $ext = '.tgz';
                    break;

                case '.bz2':
                case '.bzip2':
                    $ext = '.tbz';
                    break;
            }

            $ext = strtolower(ltrim($ext, '.'));
            if (File_Archive::isKnownExtension($ext)) {
                $tmpArchiveName = $tmpDir . DIRECTORY_SEPARATOR . $file['name'];
                if (!move_uploaded_file($file['tmp_name'], $tmpArchiveName)) {
                    continue;
                }

                $reader = File_Archive::read($tmpArchiveName);
                $source = File_Archive::readArchive($ext, $reader);
                if (!PEAR::isError($source)) {
                    while ($source->next()) {
                        $destFile   = $tmpDir . DIRECTORY_SEPARATOR . basename($source->getFilename());
                        $sourceFile = $tmpArchiveName . '/' . $source->getFilename();
                        $extract    = File_Archive::extract($sourceFile, $tmpDir);
                        if (PEAR::IsError($extract)) {
                            continue;
                        }
                        $cleanFiles['photo'.$counter] = array(
                            'name'     => basename($source->getFilename()),
                            'type'     => $source->getMime(),
                            'tmp_name' => $destFile,
                            'size'     => @filesize($destFile),
                            'error'    => 0,
                        );
                        $counter++;
                    }
                }
            } else {
                $cleanFiles['photo'.$counter] = $file;
                $counter++;
            }
        }
        return $cleanFiles;
    }

}