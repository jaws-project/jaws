<?php
/**
 * Chatbox Gadget
 *
 * @category   GadgetModel
 * @package    Chatbox
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ChatboxAdminModel extends Jaws_Gadget_Model
{
    /**
     * Install Chatbox gadget in Jaws
     *
     * @access  public
     * @return  bool    True on successful installation
     */
    function InstallGadget()
    {
        // Registry keys.
        $this->AddRegistry('limit', '7');
        $this->AddRegistry('use_antispam', 'true');
        $this->AddRegistry('max_strlen', '125');
        $this->AddRegistry('comment_status', 'approved');
        $this->AddRegistry('anon_post_authority', 'true');

        return true;
    }

    /**
     * Uninstall the gadget
     *
     * @access  public
     * @return  bool    True
     */
    function UninstallGadget()
    {
        // Registry keys
        $this->DelRegistry('limit');
        $this->DelRegistry('use_antispam');
        $this->DelRegistry('max_strlen');
        $this->DelRegistry('comment_status');
        $this->DelRegistry('anon_post_authority');

        return true;
    }

   /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool    True
     */
    function UpdateGadget($old, $new)
    {
        /*
        $result = $this->installSchema('schema.xml', '', "$old.xml");
        if (Jaws_Error::IsError($result)) {
            return $result;
        }
        */

        // Registry keys.
        $this->AddRegistry('max_strlen', '125');

        if (version_compare($old, '0.8.1', '<')) {
            $this->AddRegistry('comment_status', 'approved');
            $this->AddRegistry('anon_post_authority', 'true');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Chatbox/ManageComments',  'false');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/Chatbox/DeleteEntry');
        }

        return true;
    }

    /**
     * Mark as different status an entry
     *
     * @access  public
     * @param   array   $ids     Id's of the entries to mark as spam
     * @param   string  $status  New status (spam by default)
     * @return  bool    TRUE
     */
    function MarkCommentsAs($ids, $status = 'spam')
    {
        if (count($ids) == 0 || empty($status)) {
            return true;
        }

        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'AdminModel');
        $cModel->MarkAs($this->_Gadget, $ids, $status);
        $GLOBALS['app']->Session->PushLastResponse(_t('CHATBOX_COMMENT_MARKED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete a comment
     *
     * @access  public
     * @param   string  $id     Comment id
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function DeleteComment($id)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'AdminModel');
        $comment = $cModel->GetComment($this->_Gadget, $id);
        if (Jaws_Error::IsError($comment)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CHATBOX_ERROR_ENTRY_NOT_DELETE'), RESPONSE_ERROR);
            return new Jaws_Error(_t('CHATBOX_ERROR_ENTRY_NOT_DELETE'), _t('CHATBOX_NAME'));
        }

        $res = $cModel->DeleteComment($this->_Gadget, $id);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CHATBOX_ERROR_ENTRY_NOT_DELETE'), RESPONSE_ERROR);
            return new Jaws_Error(_t('CHATBOX_ERROR_ENTRY_NOT_DELETE'), _t('CHATBOX_NAME'));
        }

        return true;
    }

    /**
     * Does a massive entry delete
     *
     * @access  public
     * @param   array   $ids    Ids of entries
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function MassiveCommentDelete($ids)
    {
        if (!is_array($ids)) {
            $ids = func_get_args();
        }

        foreach($ids as $id) {
            $res = $this->DeleteComment($id);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('CHATBOX_ERROR_COMMENT_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('CHATBOX_ERROR_COMMENT_NOT_DELETED'), _t('BLOG_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CHATBOX_ENTRY_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates a comment
     *
     * @access  public
     * @param   string  $id         Comment id
     * @param   string  $name       Name of the author
     * @param   string  $url        Url of the author
     * @param   string  $email      Email of the author
     * @param   string  $comments   Text of the comment
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function UpdateComment($id, $name, $url, $email, $comments)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'AdminModel');
        $prev = $cModel->GetComment($this->_Gadget, $id);
        if (Jaws_Error::IsError($prev)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CHATBOX_ERROR_COMMENT_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('CHATBOX_ERROR_COMMENT_NOT_UPDATED'), _t('CHATBOX_NAME'));
        }

        $max_strlen = (int)$this->GetRegistry('max_strlen');
        $params              = array();
        $params['id']        = $id;
        $params['name']      = strip_tags($name);
        $params['title']     = strip_tags($GLOBALS['app']->UTF8->substr($comments,0, $max_strlen).'...');
        $params['url']       = strip_tags($url);
        $params['email']     = strip_tags($email);
        $params['comments']  = strip_tags($comments);
        $params['permalink'] = $permalink = $GLOBALS['app']->GetSiteURL();
        $params['status']    = $prev['status'];

        $res = $cModel->UpdateComment(
            $this->_Gadget,
            $params['id'],
            $params['name'],
            $params['email'],
            $params['url'],
            $params['title'],
            $params['comments'],
            $params['permalink'],
            $params['status']
        );
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CHATBOX_ERROR_COMMENT_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('CHATBOX_ERROR_COMMENT_NOT_UPDATED'), _t('CHATBOX_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CHATBOX_COMMENT_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Set the properties of the gadget
     *
     * @access  public
     * @param   int     $limit      Limit of chatbox entries
     * @param   int     $max_strlen Maximum length of comment entry
     * @param   bool    $authority
     * @return  mixed   True if change was successful, if not, returns Jaws_Error on any error
     */
    function UpdateProperties($limit, $max_strlen, $authority)
    {
        $res = $this->SetRegistry('limit', $limit);
        $res = $res && $this->SetRegistry('max_strlen', $max_strlen);
        $res = $res && $this->SetRegistry('anon_post_authority', ($authority == true)? 'true' : 'false');
        if ($res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CHATBOX_ERROR_SETTINGS_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('CHATBOX_ERROR_SETTINGS_NOT_UPDATED'), _t('CHATBOX_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('CHATBOX_SETTINGS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}
