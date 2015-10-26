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
class Phoo_Model_Settings extends Jaws_Gadget_Model
{
    /**
     * Get registry settings for Phoo
     *
     * @access  public
     * @return  mixed    array with the settings or Jaws_Error on error
     */
    function GetSettings()
    {
        $ret = array();
        $ret['default_action']    = $this->gadget->registry->fetch('default_action');
        $ret['resize_method']     = $this->gadget->registry->fetch('resize_method');
        $ret['moblog_album']      = $this->gadget->registry->fetch('moblog_album');
        $ret['moblog_limit']      = $this->gadget->registry->fetch('moblog_limit');
        $ret['photoblog_album']   = $this->gadget->registry->fetch('photoblog_album');
        $ret['photoblog_limit']   = $this->gadget->registry->fetch('photoblog_limit');
        $ret['allow_comments']    = $this->gadget->registry->fetch('allow_comments');
        $ret['published']         = $this->gadget->registry->fetch('published');
        $ret['show_exif_info']    = $this->gadget->registry->fetch('show_exif_info');
        $ret['keep_original']     = $this->gadget->registry->fetch('keep_original');
        $ret['thumbnail_limit']   = $this->gadget->registry->fetch('thumbnail_limit');
        $ret['comment_status']    = $this->gadget->registry->fetch('comment_status');
        $ret['use_antispam']      = $this->gadget->registry->fetch('use_antispam');
        $ret['albums_order_type'] = $this->gadget->registry->fetch('albums_order_type');
        $ret['photos_order_type'] = $this->gadget->registry->fetch('photos_order_type');

        foreach ($ret as $r) {
            if (Jaws_Error::IsError($r)) {
                if (isset($GLOBALS['app']->Session)) {
                    $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_FETCH_SETTINGS'), RESPONSE_ERROR);
                }
                return new Jaws_Error(_t('PHOO_ERROR_CANT_FETCH_SETTINGS'));
            }
        }

        return $ret;
    }
}