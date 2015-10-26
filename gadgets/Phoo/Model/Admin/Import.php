<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Model_Admin_Import extends Jaws_Gadget_Model
{
    /**
     * Items to import, looking in 'data/phoo/import' folder
     *
     * @return  array    Items to import
     */
    function GetItemsToImport()
    {
        $items = array();
        $path =  JAWS_DATA . 'phoo/import';
        if (is_dir($path)) {
            ///FIXME use scandir
            $d = dir($path);
            while (false !== ($file = $d->read())) {
                if (!is_dir($file) && (preg_match("/\.png$|\.jpg$|\.jpeg$|\.gif$/i", $file)))
                {
                    $items[] = $file;
                }
            }
            $d->close();
            return $items;
        }

        return array();
    }

}