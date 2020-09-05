<?php
/**
 * Languages Installer
 *
 * @category    GadgetModel
 * @package     Languages
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Languages_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('base_lang', 'en'),
        array('update_default_lang', 'false'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ModifyLanguageProperties',
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        if (!Jaws_Utils::is_writable(ROOT_DATA_PATH)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_DIRECTORY_UNWRITABLE', ROOT_DATA_PATH));
        }

        $new_dir = ROOT_DATA_PATH . 'languages' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_CREATING_DIR', $new_dir));
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
        if (!Jaws_Utils::is_writable(ROOT_DATA_PATH)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_DIRECTORY_UNWRITABLE', ROOT_DATA_PATH));
        }

        $new_dir = ROOT_DATA_PATH . 'languages' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        // Registry keys
        $this->gadget->registry->delete('use_data_lang');
        $this->gadget->registry->insert('update_default_lang', 'false');

        // ACL keys
        $this->gadget->acl->insert('ModifyLanguageProperties');

        return true;
    }

}