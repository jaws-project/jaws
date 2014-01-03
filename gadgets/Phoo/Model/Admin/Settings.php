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
class Phoo_Model_Admin_Settings extends Jaws_Gadget_Model
{
    /**
     * Update registry settings for phoo
     *
     * @access  public
     * @param   string  $default_action
     * @param   bool    $published
     * @param   bool    $allow_comments
     * @param   string  $moblog_album
     * @param   string  $moblog_limit
     * @param   string  $photoblog_album
     * @param   string  $photoblog_limit
     * @param   bool    $show_exif_info
     * @param   bool    $keep_original
     * @param   string  $thumb_limit
     * @param   string  $comment_status
     * @param   string  $albums_order_type
     * @param   string  $photos_order_type
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function SaveSettings($default_action, $published, $allow_comments, $moblog_album, $moblog_limit,
                          $photoblog_album, $photoblog_limit, $show_exif_info, $keep_original, $thumb_limit,
                          $comment_status, $albums_order_type, $photos_order_type)
    {
        $rs = array();
        $rs[] = $this->gadget->registry->update('default_action',    $default_action);
        $rs[] = $this->gadget->registry->update('published',         $published);
        $rs[] = $this->gadget->registry->update('allow_comments',    $allow_comments);
        $rs[] = $this->gadget->registry->update('moblog_album',      (int)$moblog_album);
        $rs[] = $this->gadget->registry->update('moblog_limit',      $moblog_limit);
        $rs[] = $this->gadget->registry->update('photoblog_album',   $photoblog_album);
        $rs[] = $this->gadget->registry->update('photoblog_limit',   $photoblog_limit);
        $rs[] = $this->gadget->registry->update('show_exif_info',    $show_exif_info);
        $rs[] = $this->gadget->registry->update('keep_original',     $keep_original);
        $rs[] = $this->gadget->registry->update('thumbnail_limit',   $thumb_limit);
        $rs[] = $this->gadget->registry->update('comment_status',    $comment_status);
        $rs[] = $this->gadget->registry->update('albums_order_type', $albums_order_type);
        $rs[] = $this->gadget->registry->update('photos_order_type', $photos_order_type);

        foreach ($rs as $r) {
            if (Jaws_Error::IsError($r) || $r === false) {
                $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_UPDATE_SETTINGS'), RESPONSE_ERROR);
                return new Jaws_Error(_t('PHOO_ERROR_CANT_UPDATE_SETTINGS'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_SETTINGS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}