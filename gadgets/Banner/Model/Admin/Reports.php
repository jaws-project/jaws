<?php
/**
 * Banner Admin Model
 *
 * @category   GadgetModel
 * @package    Banner
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Banner_Model_Admin_Reports extends Jaws_Gadget_Model
{

    /**
     * Reset banner's views counter
     *
     * @access  public
     * @param   int     $bid    banner ID
     * @return  bool    True if successful, False otherwise
     */
    function ResetViews($bid)
    {
        $model = $GLOBALS['app']->LoadGadget('Banner', 'Model', 'Banners');
        $banner = $model->GetBanner($bid);
        if (Jaws_Error::IsError($banner)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($banner['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_BANNERS_ERROR_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $bgData['views']        = 0;
        $bgData['updatetime']   = $GLOBALS['db']->Date();

        $bannersTable = Jaws_ORM::getInstance()->table('banners');
        $result = $bannersTable->update($bgData)->where('id', $bid)->exec();

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_BANNERS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Reset banner's clicks counter
     *
     * @access  public
     * @param   int     $bid    banner ID
     * @return  bool    True if successful, False otherwise
     */
    function ResetClicks($bid)
    {
        $model = $GLOBALS['app']->LoadGadget('Banner', 'Model', 'Banners');
        $banner = $model->GetBanner($bid);
        if (Jaws_Error::IsError($banner)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($banner['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_BANNERS_ERROR_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $bgData['clicks']       = 0;
        $bgData['updatetime']   = $GLOBALS['db']->Date();

        $bannersTable = Jaws_ORM::getInstance()->table('banners');
        $result = $bannersTable->update($bgData)->where('id', $bid)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('BANNER_BANNERS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

}