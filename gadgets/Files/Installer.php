<?php
/**
 * Files Installer
 *
 * @category    GadgetModel
 * @package     Files
 */
class Files_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('fm_driver', 'File'),  // Filesystem management driver
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageFiles',
    );

    /**
     * Install the gadget
     *
     * @access  public
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function Install()
    {
        $dir = ROOT_DATA_PATH . 'files/';
        if (!Jaws_FileManagement_File::mkdir($dir)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_CREATING_DIR', $dir));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Add listener for remove files items related to given gadget
        $this->gadget->event->insert('UninstallGadget');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  bool    Success/Failure (Jaws_Error)
     */
    function Uninstall()
    {
        $tables = array(
            'files',
        );
        foreach ($tables as $table) {
            $result = Jaws_DB::getInstance()->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $errMsg = Jaws::t('ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
                return new Jaws_Error($errMsg);
            }
        }

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool    Success/Failure (Jaws_Error)
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.3.0', '<')) {
            $result = $this->installSchema('schema.xml', array(), '0.2.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '0.4.0', '<')) {
            $this->gadget->registry->insert('fm_driver', 'File');
        }

        return true;
    }

}