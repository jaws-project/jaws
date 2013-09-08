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
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        'limit'               => '7',
        'use_antispam'        => 'true',
        'max_strlen'          => '125',
        'comment_status'      => 'approved',
        'anon_post_authority' => 'true',
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageComments',
        'UpdateProperties',
    );

    /**
     * Install Shoutbox gadget in Jaws
     *
     * @access  public
     * @return  bool    True on successful installation
     */
    function Install()
    {
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
        // Update layout actions
        $layoutModel = $GLOBALS['app']->loadGadget('Layout', 'AdminModel', 'Layout');
        if (!Jaws_Error::isError($layoutModel)) {
            $layoutModel->EditGadgetLayoutAction('Shoutbox', 'Display', 'Comments', 'Comments');
        }

        return true;
    }

}