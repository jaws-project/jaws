<?php
/**
 * Poll Gadget
 *
 * @category   GadgetModel
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_Model_Admin_Answer extends Poll_Model_Group
{
    /**
     * Insert a new answer
     *
     * @access  public
     * @param   int     $pid        Poll's ID
     * @param   string  $title      Answer
     * @param   string  $order
     * @return  mixed   True if the answer was created and Jaws_Error on error
     */
    function InsertAnswer($pid, $title, $order)
    {
        $data = array();
        $data['poll'] = $pid;
        $data['title'] = $title;
        $data['order'] = (int)$order;

        $table = Jaws_ORM::getInstance()->table('poll_answers');
        $result = $table->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('POLL_ERROR_ANSWER_NOT_ADDED'));
        }

        return true;
    }

    /**
     * Updates the answer
     *
     * @access  public
     * @param   string  $aid        Answer's Question
     * @param   int     $title      title
     * @param   string  $order
     * @return  mixed   True if the answer was updated and Jaws_Error on error
     */
    function UpdateAnswer($aid, $title, $order)
    {
        $data = array();
        $data['title'] = $title;
        $data['order'] = (int)$order;

        $table = Jaws_ORM::getInstance()->table('poll_answers');
        $result = $table->update($data)->where('id', $aid)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('POLL_ERROR_ANSWER_NOT_UPDATED'));
        }

        return true;
    }

    /**
     * Deletes an answer
     *
     * @access  public
     * @param   int     $aid    Answer's ID
     * @return  mixed   True if the answer was deleted and Jaws_Error on error
     */
    function DeleteAnswer($aid)
    {
        $table = Jaws_ORM::getInstance()->table('poll_answers');
        $result = $table->delete()->where('id', $aid)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLL_ERROR_ANSWER_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLL_ERROR_ANSWER_NOT_DELETED'));
        }

        return true;
    }

}