<?php
/**
 * FeedReader Installer
 *
 * @category    GadgetModel
 * @package     FeedReader
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2020 Jaws Development Group
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
        if (!$this->app->fileManagement::is_writable(ROOT_DATA_PATH)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_DIRECTORY_UNWRITABLE', ROOT_DATA_PATH));
        }

        $new_dir = ROOT_DATA_PATH . 'feedcache' . DIRECTORY_SEPARATOR;
        if (!$this->app->fileManagement::mkdir($new_dir)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_CREATING_DIR', $new_dir));
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
        $result = Jaws_DB::getInstance()->dropTable('feeds');
        if (Jaws_Error::IsError($result)) {
            $errMsg = Jaws::t('ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
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
        if (version_compare($old, '0.9.0', '<')) {
            $result = $this->installSchema('0.9.0.xml', array(), '0.8.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $new_feed_dir = ROOT_DATA_PATH. 'feedcache'. DIRECTORY_SEPARATOR;
            $old_feed_dir = ROOT_DATA_PATH. 'rsscache'.  DIRECTORY_SEPARATOR;
            if (!$this->app->fileManagement::mkdir($new_feed_dir)) {
                return new Jaws_Error(Jaws::t('ERROR_FAILED_CREATING_DIR', $new_feed_dir));
            }

            $this->app->fileManagement::delete($old_feed_dir);

            // ACL keys
            $this->gadget->acl->delete('ManageRSSSite');

            // Update layout actions
            $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->EditGadgetLayoutAction('FeedReader', 'Display', 'DisplayFeeds', 'Feed');
            }
        }

        if (version_compare($old, '1.1.0', '<')) {
            $result = $this->installSchema('1.0.0.xml', array(), '0.9.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $result = Jaws_ORM::getInstance()
                ->table('feeds')
                ->update(array('published' => false))
                ->where('visible', 0)
                ->exec();
            if (Jaws_Error::IsError($result)) {
                // do nothing
            }

            $result = $this->installSchema('schema.xml', array(), '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

}