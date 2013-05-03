<?php
/**
 * FileBrowser Installer
 *
 * @category    GadgetModel
 * @package     FileBrowser
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_Installer extends Jaws_Gadget_Installer
{
    /**
     * Install the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'files' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('FILEBROWSER_NAME'));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //registry keys.
        $this->gadget->registry->insert('black_list', 'htaccess');
        $this->gadget->registry->insert('root_dir', 'files');
        $this->gadget->registry->insert('frontend_avail', 'true');
        $this->gadget->registry->insert('virtual_links', 'false');
        $this->gadget->registry->insert('order_type', 'filename, false');
        $this->gadget->registry->insert('views_limit', '0');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function Uninstall()
    {
        $result = $GLOBALS['db']->dropTable('filebrowser');
        if (Jaws_Error::IsError($result)) {
            $gName  = _t('FILEBROWSER_NAME');
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
            $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
            return new Jaws_Error($errMsg, $gName);
        }

        // Registry keys
        $this->gadget->registry->delete('black_list');
        $this->gadget->registry->delete('root_dir');
        $this->gadget->registry->delete('frontend_avail');
        $this->gadget->registry->delete('virtual_links');
        $this->gadget->registry->delete('order_type');
        $this->gadget->registry->delete('views_limit');

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.7.0', '<')) {
            $result = $GLOBALS['db']->dropTable('filebrowser_communities');
            if (Jaws_Error::IsError($result)) {
                // do nothing
            }

            // Registry keys.
            $this->gadget->registry->insert('black_list', '.htaccess');
            $this->gadget->registry->insert('frontend_avail', 'true');
        }

        if (version_compare($old, '0.7.1', '<')) {
            $this->gadget->registry->insert('root_dir', 'files');
        }

        if (version_compare($old, '0.8.0', '<')) {
            $result = $this->installSchema('0.8.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/FileBrowser/ManageFiles',       'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/FileBrowser/UploadFiles',       'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/FileBrowser/ManageDirectories', 'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/FileBrowser/OutputAccess',      'true');
            
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/AddFile');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/RenameFile');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/DeleteFile');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/AddDir');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/RenameDir');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/DeleteDir');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/ShareDir');

            //Registry key
            $this->gadget->registry->insert('virtual_links', 'false');
            $this->gadget->registry->insert('order_type', 'filename, false');
        }

        if (version_compare($old, '0.8.1', '<')) {
            //Registry key
            $this->gadget->registry->insert('views_limit', '0');
        }

        if (version_compare($old, '0.8.2', '<')) {
            $result = $this->installSchema('schema.xml', '', "0.8.0.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '0.8.3', '<')) {
            $this->gadget->registry->update('black_list', 'htaccess');
        }

        return true;
    }

}