<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Model_Moblog extends Phoo_Model
{
    /**
     * Get entries as Moblog
     *
     * @access  public
     * @return  mixed   Returns an array of phoo entries in moblog format and Jaws_Error on error
     */
    function GetMoblog($album)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_image_album');
        $table->select('phoo_album_id', 'filename', 'phoo_image.id',
            'phoo_image.title', 'phoo_image.description', 'phoo_image.createtime');
        $table->join('phoo_image', 'phoo_image.id', 'phoo_image_album.phoo_image_id');
        $table->join('phoo_album', 'phoo_album.id', 'phoo_image_album.phoo_album_id');
        $table->where('phoo_image.published', true)->and();
        $table->where('phoo_album.id', $album);
        $table->orderBy('phoo_image.createtime desc');

        $limit = $this->gadget->registry->fetch('moblog_limit');
        if (Jaws_Error::isError($limit)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETMOBLOG'));
        }

        $result = $table->limit($limit)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETMOBLOG'));
        }

        foreach ($result as $key => $image) {
            $result[$key]['name'] = $image['title'];
            $result[$key]['thumb'] = $this->GetThumbPath($image['filename']);
            $result[$key]['medium'] = $this->GetMediumPath($image['filename']);
            $result[$key]['image'] = $this->GetOriginalPath($image['filename']);
            $result[$key]['stripped_description'] = $image['description'];
        }

        return $result;
    }
}