<?php
/**
 * Forums Gadget
 *
 * @category   GadgetModel
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ForumsAdminModel extends Jaws_Gadget_Model
{
    /**
     * Install the gadget
     *
     * @access  public
     * @return  mixed  Success with true and failure with Jaws_Error
     */
    function InstallGadget()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $result = $this->installSchema('insert.xml', '', 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $new_dir = JAWS_DATA . 'forums';
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('FORUMS_NAME'));
        }

        // Registry keys
        $GLOBALS['app']->Registry->NewKey('/gadgets/Forums/topics_limit', '15');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Forums/posts_limit',  '10');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Forums/date_format', 'd MN Y G:i');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Forums/edit_min_limit_time', '120');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Forums/edit_max_limit_time', '600');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Forums/enable_attachment',   'true');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed  True on Success and Jaws_Error on Failure
     */
    function UninstallGadget()
    {
        $tables = array(
            'forums_posts',
            'forums_topics',
            'forums',
            'forums_groups'
        );
        $gName  = _t('FORUMS_NAME');
        $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Forums/topics_limit');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Forums/posts_limit');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Forums/date_format');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Forums/edit_min_limit_time');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Forums/edit_max_limit_time');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Forums/enable_attachment');

        return true;
    }

}