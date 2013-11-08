<?php
/**
 * Phoo Gadget
 *
 * @category   Gadget
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Default extends Jaws_Gadget_Action
{

//    /**
//     * Displays a preview of the given phoo comment
//     *
//     * @access  public
//     * @return  string  XHTML template content
//     */
//    function Preview()
//    {
//        $names = array(
//            'name', 'email', 'url', 'title', 'comments', 'createtime',
//            'ip_address', 'reference', 'albumid'
//        );
//        $post = jaws()->request->fetch($names, 'post');
//        $post['reference'] = (int)$post['reference'];
//        $post['albumid']   = (int)$post['albumid'];
//
//        $model = $this->gadget->model->load('Photos');
//        $image = $model->GetImage($post['reference'], $post['albumid']);
//        if (Jaws_Error::isError($image)) {
//            $GLOBALS['app']->Session->PushSimpleResponse($image->getMessage(), 'Phoo');
//            Jaws_Header::Location($this->gadget->urlMap('AlbumList'));
//        }
//
//        return $this->ViewImage($post['reference'], $post['albumid'], true);
//    }

//    /**
//     * Resize an image on the fly
//     *
//     * FIXME: I don't know if is better to get it as a standalone function...
//     *
//     * @returns binary Image resized
//     */
//    function Thumb()
//    {
//        $image = jaws()->request->fetch('image', 'get');
//
//        include_once JAWS_PATH . 'include/Jaws/Image.php';
//        Jaws_Image::get_exif_thumbnail(JAWS_DATA . 'phoo/import/' . $image, 'gadgets/Phoo/Resources/images/Phoo.png');
//    }

}