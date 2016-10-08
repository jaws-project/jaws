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
class Phoo_Model extends Jaws_Gadget_Model
{
    /**
     * Get the thumbnail thumb path of a given filename
     *
     * @access  public
     * @param   string  $file   Name of the file
     * @return  string  The ThumbPath
     */
    function GetThumbPath($file)
    {
        $path = substr($file, 0, strrpos($file, '/'));
        return $path . '/thumb/' . basename($file);
    }

    /**
     * Get the medium path of a given filename
     *
     * @access  public
     * @param   string  $file   Name of the file
     * @return  string  The MediumPath
     */
    function GetMediumPath($file)
    {
        $path = substr($file, 0, strrpos($file, '/'));
        return $path . '/medium/' . basename($file);
    }

    /**
     * Get the original path of a given filename
     *
     * @access  public
     * @param   string  $file   Name of the file
     * @return  string  The original path
     */
    function GetOriginalPath($file)
    {
        $path = substr($file, 0, strrpos($file, '/'));
        return $path . '/' . basename($file);
    }

    /**
     * Get the correct order type
     *
     * @access  private
     * @param   string  $resource
     * @return  string   The correct (or default) order type
     */
    function GetOrderType($resource)
    {
        $orderType = $this->gadget->registry->fetch($resource);
        if ($resource == 'photos_order_type') {
            if (!in_array($orderType, array('createtime desc', 'createtime', 'title desc', 'title', 'id desc','id' )))
            {
                $orderType = 'title';
            }
        } else {
            if (!in_array($orderType, array('createtime desc', 'createtime', 
                                        'phoo_album.name desc', 'phoo_album.name', 'phoo_album.id desc', 'phoo_album.id' )))
            {
                $orderType = 'name';
            }
        }

        if (strpos($orderType,'desc')) {
                $orderType = trim(substr($orderType, 0, strpos($orderType,'desc'))). ' desc';
        } else {
                $orderType = $orderType;
        }

        return $orderType;
    }

//    /**
//     * Performs an advanced search
//     *
//     * @access  public
//     * @param   string  $date     Entry date
//     * @param   string  $album    Album ID
//     * @param   string  $words    Words to search
//     * @return  mixed   Get an array of phoo entries that matches a pattern and Jaws_Error on error
//     */
//    function AdvancedSearch($date, $album, $words = '')
//    {
//        $table = Jaws_ORM::getInstance()->table('phoo_image');
//        $table->select('phoo_image.id', 'filename', 'title', 'createtime');
//        $table->join('phoo_image_album', 'phoo_image.id', 'phoo_image_album.phoo_image_id', 'left');
//
//        if (!empty($date)) {
//            $table->where(
//                $table->substring('phoo_image.filename', 1, 10),
//                str_replace('-', '_', $date)
//            )->and();
//        }
//
//        if (!empty($album)) {
//            $table->where('phoo_image_album.phoo_album_id', $album)->and();
//        }
//
//        if (!empty($words)) {
//            $words = explode(' ', $words);
//            foreach ($words as $word) {
//                $table->where('phoo_image.title', $word, 'like');
//            }
//        }
//
//        //echo $table->fetchRaw();
//        return $table->orderBy('createtime desc')->fetchAll();
//    }
//




}
