<?php
/**
 * PrivateMessage Gadget
 *
 * @category    GadgetModel
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class PrivateMessage_Model_Outbox extends Jaws_Gadget_Model
{
    /**
     * Get Outbox
     *
     * @access  public
     * @param   integer  $user          User id
     * @param   boolean  $published
     * @return  mixed    Inbox content  or Jaws_Error on failure
     */
    function GetOutbox($user, $published = true)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select(
            'pm_messages.id:integer','pm_messages.subject', 'pm_messages.body', 'pm_messages.insert_time',
            'users.nickname as from_nickname'
        );
        $table->join('users', 'pm_messages.user', 'users.id');
        $result = $table->where('pm_messages.user', $user)->and()->where('published', $published)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get Outbox Statistics
     *
     * @access  public
     * @param   integer  $user          User id
     * @param   integer  $published
     * @return  mixed    Inbox count or Jaws_Error on failure
     */
    function GetOutboxStatistics($user, $published)
    {
        $table = Jaws_ORM::getInstance()->table('pm_messages');
        $table->select('count(id):integer')->where('user', $user);
        $result = $table->and()->where('pm_messages.published', $published)->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

}