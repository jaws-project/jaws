<?php
/**
 * FeedReader Installer
 *
 * @category    GadgetModel
 * @package     FeedReader
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class FeedReader_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('default_feed', '0'),
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   true on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'feedcache' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   true on success, Jaws_Error otherwise
     */
    function Uninstall()
    {
        $result = $GLOBALS['db']->dropTable('feeds');
        if (Jaws_Error::IsError($result)) {
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
            return new Jaws_Error($errMsg);
        }

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   true on success, Jaws_Error otherwise
     */
    function Upgrade($old, $new)
    {
        $result = $this->installSchema('schema.xml', '', '0.8.0.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $new_feed_dir = JAWS_DATA. 'feedcache'. DIRECTORY_SEPARATOR;
        $old_feed_dir = JAWS_DATA. 'rsscache'.  DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_feed_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_feed_dir));
        }

        Jaws_Utils::delete($old_feed_dir);

        // ACL keys
        $this->gadget->acl->delete('ManageRSSSite');

        // Update layout actions
        $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
        if (!Jaws_Error::isError($layoutModel)) {
            $layoutModel->EditGadgetLayoutAction('FeedReader', 'Display', 'DisplayFeeds', 'Feed');
        }

        return true;
    }

}