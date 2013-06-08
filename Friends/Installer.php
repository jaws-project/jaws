<?php
/**
 * Friends Installer
 *
 * @category    GadgetModel
 * @package     Friends
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Friends_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'AddFriend',
        'EditFriend',
        'DeleteFriend',
        'UpdateProperties',
    );

    /**
     * Install the gadget
     *
     * @access  public
     * @return  mixed   Returns true if installation success and Jaws_Error on any error found
     */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //registry keys.
        $this->gadget->registry->insert('limit', '5');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed  True on Success or Jaws_Error on Failure
     */
    function Uninstall()
    {
        $result = $GLOBALS['db']->dropTable('friend');
        if (Jaws_Error::IsError($result)) {
            $gName  = _t('FRIENDS_NAME');
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
            $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
            return new Jaws_Error($errMsg, $gName);
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
        return true;
    }

}