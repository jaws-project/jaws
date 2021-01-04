<?php
/**
 * PrivateMessage Installer
 *
 * @category    GadgetModel
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2008-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('date_format', 'DN d MN Y g:i a'),
        array('paging_limit', '10'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageProperties',
        'DeleteMessage',
        'ArchiveMessage',
        'ReplyMessage',
        'SendMessage',
    );

    /**
     * Default ACL value of frontend gadget access
     *
     * @var     bool
     * @access  protected
     */
    var $default_acl = false;

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $new_dir = ROOT_DATA_PATH . 'pm';
        if (!Jaws_FileManagement_File::mkdir($new_dir)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        $new_dir = ROOT_DATA_PATH . 'pm' . DIRECTORY_SEPARATOR . 'attachments';
        if (!Jaws_FileManagement_File::mkdir($new_dir)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        $this->gadget->event->insert('Notify');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error otherwise
     */
    function Uninstall()
    {
        $tables = array(
            'pm_message_attachment',
            'pm_messages',
            'pm_attachments',
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
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '1.1.0', '<')) {
            $result = $this->installSchema('schema.xml', array(), '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

}