<?php
/**
 * Poll Gadget
 *
 * @category   GadgetModel
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_Model_Admin_Group extends Poll_Model_Group
{
    /**
     * Insert a poll group
     *
     * @access  public
     * @param    string  $title      group title
     * @param    bool    $published  published
     * @return   bool    True on Success or False Failure
     */
    function InsertPollGroup($title, $published)
    {
        $table = Jaws_ORM::getInstance()->table('poll_groups');
        $count = $table->select('COUNT([id])')->where('title', $title)->fetchOne();
        if (Jaws_Error::IsError($count)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($count > 0) {
            $this->gadget->session->push($this::t('ERROR_GROUP_TITLE_DUPLICATE'), RESPONSE_ERROR);
            return false;
        }

        $data = array();
        $data['title']   = $title;
        $data['published'] = $published;
        $table->reset();
        $result = $table->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_GROUP_NOT_ADDED'));
        }

        $this->gadget->session->push($this::t('GROUPS_CREATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Update a poll group
     *
     * @access  public
     * @param    int     $gid        group ID
     * @param    string  $title      group title
     * @param    bool    $published  published
     * @return   mixed   True on Success, Jaws_Error or False on Failure
     */
    function UpdatePollGroup($gid, $title, $published)
    {
        $table = Jaws_ORM::getInstance()->table('poll_groups');
        $gc = $table->select('COUNT([id])')
            ->where('id', $gid, '!=')->and()
            ->where('title', $title)->fetchOne();
        if (Jaws_Error::IsError($gc)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if ($gc > 0) {
            $this->gadget->session->push($this::t('ERROR_GROUP_TITLE_DUPLICATE'), RESPONSE_ERROR);
            return false;
        }

        $data = array();
        $data['title'] = $title;
        $data['published'] = $published;
        $table->reset();
        $result = $table->update($data)->where('id', $gid)->exec();
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_GROUP_NOT_UPDATED'));
        }

        $this->gadget->session->push($this::t('GROUPS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete a poll group
     *
     * @access  public
     * @param   int     $gid    The poll group that will be deleted
     * @return  mixed   True if query was successful and Jaws_Error or False on error
     */
    function DeletePollGroup($gid)
    {
        if ($gid == 1) {
            $this->gadget->session->push($this::t('ERROR_GROUP_NOT_DELETED'), RESPONSE_ERROR);
            return false;
        }

        $group = $this->GetPollGroup($gid);
        if (Jaws_Error::IsError($group)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($group['id'])) {
            $this->gadget->session->push($this::t('ERROR_GROUP_DOES_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $model = $this->gadget->model->loadAdmin('Poll');
        $model->UpdateGroupsOfPolls(-1, $gid, 0);

        $table = Jaws_ORM::getInstance()->table('poll_groups');
        $result = $table->delete()->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_GROUP_NOT_DELETED'));
        }

        $this->gadget->session->push($this::t('GROUPS_DELETED', $gid), RESPONSE_NOTICE);
        return true;
    }
}