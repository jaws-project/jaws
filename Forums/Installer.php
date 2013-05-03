<?php
/**
 * Forums Installer
 *
 * @category    GadgetModel
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Installer extends Jaws_Gadget_Installer
{
    /**
     * Install the gadget
     *
     * @access  public
     * @return  mixed  Success with true and failure with Jaws_Error
     */
    function Install()
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
        $this->gadget->registry->insert('topics_limit', '15');
        $this->gadget->registry->insert('posts_limit',  '10');
        $this->gadget->registry->insert('recent_limit',  '5');
        $this->gadget->registry->insert('date_format', 'd MN Y G:i');
        $this->gadget->registry->insert('edit_min_limit_time', '300');
        $this->gadget->registry->insert('edit_max_limit_time', '900');
        $this->gadget->registry->insert('enable_attachment',   'true');

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
        $this->gadget->registry->delete('topics_limit');
        $this->gadget->registry->delete('posts_limit');
        $this->gadget->registry->delete('recent_limit');
        $this->gadget->registry->delete('date_format');
        $this->gadget->registry->delete('edit_min_limit_time');
        $this->gadget->registry->delete('edit_max_limit_time');
        $this->gadget->registry->delete('enable_attachment');

        return true;
    }

}