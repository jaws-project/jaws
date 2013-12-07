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
class Phoo_Model_Photoblog extends Phoo_Model
{
    /**
     * Get a portrait of an image
     *
     * @access  public
     * @param   string  $id     ID of the image
     * @return  mixed   An array with the images with a portrait look&feel and Jaws_Error on error
     */
    function GetAsPortrait($id = '')
    {
        $table = Jaws_ORM::getInstance()->table('phoo_image_album');
        $table->select(
            'filename',
            'phoo_image.id',
            'phoo_image.title',
            'phoo_image.description',
            'phoo_image.createtime');
        $table->join('phoo_image', 'phoo_image.id',
            'phoo_image_album.phoo_image_id');
        $table->join('phoo_album', 'phoo_album.id',
            'phoo_image_album.phoo_album_id');
        $table->where('phoo_image.published', true);
        $table->and()->openWhere();
        $album = $this->gadget->registry->fetch('photoblog_album');
        foreach (explode(',', $album) as $v) {
            $table->where('phoo_album.name', $v);
            $table->or();
        }
        $table->closeWhere();
        $table->orderBy('phoo_image.id desc');
        $table->limit($this->gadget->registry->fetch('photoblog_limit'));
        $result = $table->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETASPORTRAIT'));
        }

        include_once JAWS_PATH . 'include/Jaws/Image.php';
        $portrait = array();
        foreach ($result as $r) {
            $info = array();

            $info['id']          = $r['id'];
            $info['name']        = $r['title'];
            $info['filename']    = $r['filename'];
            $info['description'] = $r['description'];
            $info['createtime']  = $r['createtime'];
            $info['thumb']       = $this->GetThumbPath($r['filename']);
            $info['medium']      = $this->GetMediumPath($r['filename']);
            $info['image']       = $this->GetOriginalPath($r['filename']);
            $info['stripped_description'] = strip_tags($r['description']);

            $portrait[] = $info;
        }

        return $portrait;
    }
}