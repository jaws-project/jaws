<?php
/**
 * Preferences Installer
 *
 * @category    GadgetModel
 * @package     Preferences
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Preferences_Installer extends Jaws_Gadget_Installer
{
    /**
     * Install the gadget
     *
     * @access  public
     * @return  bool    true on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $this->gadget->AddRegistry('display_theme',             'true');
        $this->gadget->AddRegistry('display_editor',            'true');
        $this->gadget->AddRegistry('display_language',          'true');
        $this->gadget->AddRegistry('display_calendar_type',     'true');
        $this->gadget->AddRegistry('display_calendar_language', 'true');
        $this->gadget->AddRegistry('display_date_format',       'true');
        $this->gadget->AddRegistry('display_timezone',          'true');

        //enable cookie precedence
        $this->gadget->SetRegistry('cookie_precedence', 'true', 'Settings');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  bool     Success/Failure (Jaws_Error)
     */
    function Uninstall()
    {
        // registry keys
        $this->gadget->DelRegistry('display_theme');
        $this->gadget->DelRegistry('display_editor');
        $this->gadget->DelRegistry('display_language');
        $this->gadget->DelRegistry('display_calendar_type');
        $this->gadget->DelRegistry('display_calendar_language');
        $this->gadget->DelRegistry('display_date_format');
        $this->gadget->DelRegistry('display_timezone');

        //disable cookie precedence
        $this->gadget->SetRegistry('cookie_precedence', 'false', 'Settings');

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool     Success/Failure (Jaws_Error)
     */
    function Upgrade($old, $new)
    {
        // ACL keys
        $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Preferences/UpdateProperties',   'true');
        $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Preferences/ChangeSettings');

        // Registry keys.
        $this->gadget->AddRegistry('display_editor',            'true');
        $this->gadget->AddRegistry('display_calendar_type',     'true');
        $this->gadget->AddRegistry('display_calendar_language', 'true');
        $this->gadget->AddRegistry('display_date_format',       'true');
        $this->gadget->AddRegistry('display_timezone',          'true');

        //enable cookie precedence
        $this->gadget->SetRegistry('cookie_precedence', 'true', 'Settings');

        return true;
    }

}