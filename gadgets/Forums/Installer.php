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
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        'topics_limit' => '15',
        'posts_limit'  =>  '10',
        'recent_limit' =>  '5',
        'date_format'  => 'd MN Y G:i',
        'edit_min_limit_time' => '300',
        'edit_max_limit_time' => '900',
        'enable_attachment'   => 'true',
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'AddForum',
        'EditForum',
        'LockForum',
        'DeleteForum',
        'AddTopic',
        'PublishTopic',
        'EditTopic',
        'MoveTopic',
        'EditOthersTopic',
        'EditLockedTopic',
        'EditOutdatedTopic',
        'LockTopic',
        'DeleteTopic',
        'DeleteOthersTopic',
        'DeleteOutdatedTopic',
        'AddPost',
        'PublishPost',
        'AddPostAttachment',
        'AddPostToLockedTopic',
        'EditPost',
        'EditOthersPost',
        'EditPostInLockedTopic',
        'EditOutdatedPost',
        'DeletePost',
        'DeleteOthersPost',
        'DeleteOutdatedPost',
        'DeletePostInLockedTopic',
    );

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
                return new Jaws_Error($errMsg, $gName);
            }
        }

        return true;
    }

}