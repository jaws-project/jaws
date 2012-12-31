<?php
/**
 * Poll Installer
 *
 * @category    GadgetModel
 * @package     Poll
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Poll_Installer extends Jaws_Gadget_Installer
{
    /**
     * Install the gadget
     *
     * @access  public
     * @return  mixed  True on success and Jaws_Error on failure
     */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $result = $this->installSchema('insert.xml', null, 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys.
        $this->gadget->AddRegistry('cookie_period',  '150');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed  True on Success and Jaws_Error on Failure
     */
    function Uninstall()
    {
        $tables = array('poll',
                        'poll_groups',
                        'poll_answers');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('POLL_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $this->gadget->DelRegistry('cookie_period');

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
        if (version_compare($old, '0.8.0', '<')) {
            $result = $this->installSchema('0.8.0.xml', '', "$old.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $result = $this->installSchema('insert.xml', '', '0.8.0.xml', true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Poll/ManagePolls',  'true');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Poll/ManageGroups', 'true');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Poll/ViewReports',  'true');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Poll/AddPoll');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Poll/EditPoll');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Poll/DeletePoll');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Poll/UpdateProperties');

            // Registry keys.
            $this->gadget->AddRegistry('cookie_period',  '150');
        }

        $result = $this->installSchema('schema.xml', '', "0.8.0.xml");
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

}