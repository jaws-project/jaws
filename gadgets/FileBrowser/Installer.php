<?php
/**
 * FileBrowser Installer
 *
 * @category    GadgetModel
 * @package     FileBrowser
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('black_list', 'htaccess'),
        array('root_dir', 'files'),
        array('order_type', 'filename, false'),
        array('views_limit', '0'),
        array('virtual_links', 'false'),
        array('frontend_avail', 'true'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageFiles',
        'UploadFiles',
        'ManageDirectories',
        array('OutputAccess', '', true),
    );

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
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function Uninstall()
    {
        $result = Jaws_DB::getInstance()->dropTable('filebrowser');
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
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '1.0.0', '<')) {
            // removeing gadget registry keys
            $GLOBALS['app']->Registry->delete($this->gadget->name);

            // adding registry keys
            $installer->_RegKeys = array_merge(
                array(
                    array('version', '1.0.0'),
                    array('requires', ',,'),
                ),
                $this->_RegKeys
            );
            $this->gadget->registry->insertAll($installer->_RegKeys);
        }

        return true;
    }

}