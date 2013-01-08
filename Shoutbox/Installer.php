<?php
/**
 * Shoutbox Installer
 *
 * @category    GadgetModel
 * @package     Shoutbox
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_Installer extends Jaws_Gadget_Installer
{
    /**
     * Install Shoutbox gadget in Jaws
     *
     * @access  public
     * @return  bool    True on successful installation
     */
    function Install()
    {
        // Registry keys.
        $this->gadget->AddRegistry('limit', '7');
        $this->gadget->AddRegistry('use_antispam', 'true');
        $this->gadget->AddRegistry('max_strlen', '125');
        $this->gadget->AddRegistry('comment_status', 'approved');
        $this->gadget->AddRegistry('anon_post_authority', 'true');

        return true;
    }

    /**
     * Uninstall the gadget
     *
     * @access  public
     * @return  bool    True
     */
    function Uninstall()
    {
        // Registry keys
        $this->gadget->DelRegistry('limit');
        $this->gadget->DelRegistry('use_antispam');
        $this->gadget->DelRegistry('max_strlen');
        $this->gadget->DelRegistry('comment_status');
        $this->gadget->DelRegistry('anon_post_authority');

        return true;
    }

   /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool    True
     */
    function Upgrade($old, $new)
    {
        /*
        $result = $this->installSchema('schema.xml', '', "$old.xml");
        if (Jaws_Error::IsError($result)) {
            return $result;
        }
        */

        // Registry keys.
        $this->gadget->AddRegistry('max_strlen', '125');

        if (version_compare($old, '0.8.1', '<')) {
            $this->gadget->AddRegistry('comment_status', 'approved');
            $this->gadget->AddRegistry('anon_post_authority', 'true');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Shoutbox/ManageComments',  'false');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Shoutbox/DeleteEntry');
        }

        return true;
    }

}