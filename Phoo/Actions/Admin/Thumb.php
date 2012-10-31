<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Admin_Thumb extends PhooAdminHTML
{
    /**
     * Resize an image on the fly
     * 
     * FIXME: I don't know if is better to get it as a standalone function...
     * 
     * @returns binary Image resized
     */
    function Thumb()
    {
        $request =& Jaws_Request::getInstance();
        $image   = $request->get('image', 'get');

        include_once JAWS_PATH . 'include/Jaws/Image.php';
        Jaws_Image::get_exif_thumbnail(JAWS_DATA . 'phoo/import/' . $image, 'gadgets/Phoo/images/logo.png');
    }

}