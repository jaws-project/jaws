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
        $this->AddRegistry('topics_limit', '15');
        $this->AddRegistry('posts_limit',  '10');
        $this->AddRegistry('recent_limit',  '5');
        $this->AddRegistry('date_format', 'd MN Y G:i');
        $this->AddRegistry('edit_min_limit_time', '300');
        $this->AddRegistry('edit_max_limit_time', '900');
        $this->AddRegistry('enable_attachment',   'true');

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
        $this->DelRegistry('topics_limit');
        $this->DelRegistry('posts_limit');
        $this->DelRegistry('recent_limit');
        $this->DelRegistry('date_format');
        $this->DelRegistry('edit_min_limit_time');
        $this->DelRegistry('edit_max_limit_time');
        $this->DelRegistry('enable_attachment');

        return true;
    }

}