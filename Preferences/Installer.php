<?php
/**
 * Preferences Installer
 *
 * @category    GadgetModel
 * @package     Preferences
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Preferences_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'UpdateProperties',
    );

    /**
     * Install the gadget
     *
     * @access  public
     * @return  bool    true on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $this->gadget->registry->insert('display_theme',             'true');
        $this->gadget->registry->insert('display_editor',            'true');
        $this->gadget->registry->insert('display_language',          'true');
        $this->gadget->registry->insert('display_calendar_type',     'true');
        $this->gadget->registry->insert('display_calendar_language', 'true');
        $this->gadget->registry->insert('display_date_format',       'true');
        $this->gadget->registry->insert('display_timezone',          'true');

        //enable cookie precedence
        $this->gadget->registry->update('cookie_precedence', 'true', 'Settings');

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
        //disable cookie precedence
        $this->gadget->registry->update('cookie_precedence', 'false', 'Settings');

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
        return true;
    }

}