<?php
/**
 * Contact Installer
 *
 * @category    GadgetModel
 * @package     Contact
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('use_antispam', 'true'),
        array('email_format', 'html'),
        array('enable_attachment', 'false'),
        array('comments', ''),
        array('default_items', 'name,email,url,recipient,subject,attachment,message'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageContacts',
        'EditSentMessage',
        'ManageRecipients',
        'AccessToMailer',
        'UpdateProperties',
        'AllowAttachment',
    );

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

        $new_dir = JAWS_DATA . 'contact';
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir));
        }

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
        $tables = array('contacts',
                        'contacts_recipients');
        foreach ($tables as $table) {
            $result = Jaws_DB::getInstance()->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
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
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function Upgrade($old, $new)
    {
        // Update layout actions
        $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
        if (!Jaws_Error::isError($layoutModel)) {
            $layoutModel->EditGadgetLayoutAction('Contact', 'Display', 'Contact', 'Contact');
            $layoutModel->EditGadgetLayoutAction('Contact', 'DisplayMini', 'ContactMini', 'Contact');
            $layoutModel->EditGadgetLayoutAction('Contact', 'DisplaySimple', 'ContactSimple', 'Contact');
            $layoutModel->EditGadgetLayoutAction('Contact', 'DisplayFull', 'ContactFull', 'Contact');
        }

        return true;
    }

}