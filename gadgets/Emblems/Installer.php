<?php
/**
 * Emblems Installer
 *
 * @category    GadgetModel
 * @package     Emblems
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_Installer extends Jaws_Gadget_Installer
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'emblems' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // If you are here, then copy the default jaws and feeds images
        $emblems = array('jaws', 'php', 'apache', 'mysql', 'pgsql', 'xhtml', 'css', 'atom', 'rss');
        foreach ($emblems as $emblem) {
            copy(JAWS_PATH. "gadgets/Emblems/Resources/images/$emblem.png", $new_dir. "$emblem.png");
            Jaws_Utils::chmod($new_dir. "$emblem.png");
        }

        $variables = array();
        $variables['timestamp'] = Jaws_DB::getInstance()->date();

        // Dump database data
        $result = $this->installSchema('insert.xml', $variables, 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error on error
     */
    function Uninstall()
    {
        $result = Jaws_DB::getInstance()->dropTable('emblem');
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
        $this->gadget->acl->delete('AddEmblem');
        $this->gadget->acl->delete('EditEmblem');
        $this->gadget->acl->delete('DeleteEmblem');
        $this->gadget->acl->delete('UpdateProperties');
        return true;
    }
}