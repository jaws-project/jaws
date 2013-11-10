<?php
/**
 * Comments Model
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Model_EditComments extends Jaws_Gadget_Model
{
    /**
     * Message is unique? Is it not duplicated?
     *
     * @access  public
     * @param   string   $md5     Message key in MD5
     * @return  bool    Exists (true) or Not Exists (false)
     */
    function IsMessageDuplicated($md5)
    {
        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $howmany = $commentsTable->select('count([id]):integer')->where('msg_key', $md5)->fetchOne();

        ///FIXME check for errors
        return ($howmany == '0') ? false : true;
    }

    /**
     * Inserts a new comment
     *
     * @param   string  $gadget     Gadget's name
     * @param   int     $gadgetId   Gadget's reference id.
     *                              It can be the ID of a blog entry, the ID of a
     *                              photo in Phoo, etc. This needs to be a reference
     *                              to find the comments related to a specific record
     *                              in a gadget.
     * @param   string  $action
     * @param   string  $name       Author's name
     * @param   string  $email      Author's email
     * @param   string  $url        Author's url
     * @param   string  $message    Author's message
     * @param   string  $ip         Author's IP
     * @param   string  $permalink  Permanent link to resource
     * @param   int     $status
     * @param   boolean $private    Is a private message?
     * @return  int     Comment status or Jaws_Error on any error
     * @access  public
     */
    function insertComment($gadget, $gadgetId, $action, $name, $email, $url, $message,
                        $ip, $permalink, $status = Comments_Info::COMMENTS_STATUS_APPROVED, $private = null)
    {
        if (!in_array($status, array(Comments_Info::COMMENTS_STATUS_APPROVED, Comments_Info::COMMENTS_STATUS_WAITING,
            Comments_Info::COMMENTS_STATUS_SPAM, Comments_Info::COMMENTS_STATUS_PRIVATE))) {
            $status = Comments_Info::COMMENTS_STATUS_SPAM;
        }

        if ($private) {
            $status = Comments_Info::COMMENTS_STATUS_PRIVATE;
        }

        $message_key = md5($message);
        if ($this->gadget->registry->fetch('allow_duplicate') == 'no') {
            if ($this->IsMessageDuplicated($message_key)) {
                return new Jaws_Error(_t('COMMENTS_SPAM_POSSIBLE_DUPLICATE_MESSAGE'), _t('COMMENTS_NAME'));
            }
        }

        // Validate website url
        if (!preg_match('$^(http|https|ftp)://([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?/?$i', $url)) {
            $url = '';
        }

        // Comment Status...
        $mPolicy = Jaws_Gadget::getInstance('Policy')->model->load('AntiSpam');
        if ($mPolicy->IsSpam($permalink, $gadget, $name, $email, $url, $message)) {
            $status = COMMENTS_STATUS_SPAM;
        }

        $cData = array();
        $cData['reference']     = $gadgetId;
        $cData['action']        = $action;
        $cData['gadget']        = $gadget;
        $cData['name']          = $name;
        $cData['email']         = $email;
        $cData['url']           = $url;
        $cData['msg_txt']       = $message;
        $cData['status']        = (int)$status;
        $cData['msg_key']       = $message_key;
        $cData['ip']            = $ip;
        $cData['user']          = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $cData['createtime']    = $GLOBALS['db']->Date();

        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $res = $commentsTable->insert($cData)->exec();

        if (!Jaws_Error::IsError($res)) {
            $this->gadget->event->shout('UpdateComment', array($gadget, $action, $gadgetId));
        }

        return $res;
    }

    /**
     * Updates a comment
     *
     * @param   string  $gadget     Gadget's name
     * @param   int     $id         Comment's ID
     * @param   string  $name       Author's name
     * @param   string  $email      Author's email
     * @param   string  $url        Author's url
     * @param   string  $message    Author's message
     * @param   string  $reply      Comment's reply
     * @param   string  $permalink  Permanent link to resource
     * @param   string  $status     Comment status
     * @param   bool   $emailReply  Send replay as email to comment owner
     * @return  bool    True if success or Jaws_Error on any error
     * @access  public
     */
    function updateComment($gadget, $id, $name, $email, $url, $message, $reply, $permalink, $status, $emailReply = false)
    {
        $cData = array();
        $cData['name']    = $name;
        $cData['email']   = $email;
        $cData['url']     = $url;
        $cData['msg_txt'] = $message;
        $cData['msg_key'] = md5($message);
        $cData['replier'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $cData['status']  = $status;
        if($this->gadget->GetPermission('ReplyComments')) {
            $cData['reply']   = $reply;
        }

        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $result = $commentsTable->update($cData)->where('id', $id)->and()->where('gadget', $gadget)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $cModel = $this->gadget->model->load('Comments');
        $origComment = $cModel->GetComment($id);
        if (($status == Comments_Info::COMMENTS_STATUS_SPAM || $origComment['status'] == Comments_Info::COMMENTS_STATUS_SPAM) &&
            $origComment['status'] != $status)
        {
            $mPolicy = Jaws_Gadget::getInstance('Policy')->model->loadAdmin('AntiSpam');
            if ($status == Comments_Info::COMMENTS_STATUS_SPAM) {
                $mPolicy->SubmitSpam($permalink, $gadget, $name, $email, $url, $message);
            } else {
                $mPolicy->SubmitHam($permalink, $gadget, $name, $email, $url, $message);
            }
        }

        if (!Jaws_Error::IsError($res)) {
            $commentsTable = Jaws_ORM::getInstance()->table('comments');
            $commentsTable->select('gadget', 'reference:integer', 'action');
            $comment = $commentsTable->where('id', $id)->fetchRow();
            $this->gadget->event->shout('UpdateComment', array($comment['gadget'], $comment['action'],
                $comment['reference']));
        }

        if (!empty($cData['reply']) && !empty($email) && $emailReply) {
            $this->EmailReply($email, $message, $cData['reply'], $cData['replier']);
        }

        return true;
    }


    /**
     * Mails reply to the sender
     *
     * @access  public
     * @param   string  $email      Comment sender's email
     * @param   string  $message    Message
     * @param   string  $reply      Reply message
     * @param   int     $replier    Replier Id
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function EmailReply($email, $message, $reply, $replier)
    {
        $site_url   = $GLOBALS['app']->getSiteURL('/');
        $site_name  = $this->gadget->registry->fetch('site_name', 'Settings');

        $tpl = $this->gadget->template->loadAdmin('EmailReply.html');
        $tpl->SetBlock('notification');
        $tpl->SetVariable('lbl_message',  _t('COMMENTS_MESSAGE'));
        $tpl->SetVariable('message',      $message);
        $tpl->SetVariable('replier',      _t('COMMENTS_REPLY_BY', $replier));
        $tpl->SetVariable('lbl_reply',    _t('COMMENTS_REPLY'));
        $tpl->SetVariable('reply',        $reply);
        $tpl->SetVariable('site_name',    $site_name);
        $tpl->SetVariable('site_url',     $site_url);

        $tpl->ParseBlock('notification');
        $template = $tpl->Get();

        $ObjMail = new Jaws_Mail;
        $ObjMail->SetFrom();
        if (empty($email)) {
            $ObjMail->AddRecipient('', 'to');
        } else {
            $ObjMail->AddRecipient($email);
            $ObjMail->AddRecipient('', 'cc');
        }
        $ObjMail->SetSubject(_t('COMMENTS_YOU_GET_REPLY'));
        $ObjMail->SetBody($template, 'html');
        return $ObjMail->send();
    }

    /**
     * Mark as a different status several comments
     *
     * @access  public
     * @param   string  $gadget  Gadget's name
     * @param   array   $ids     Id's of the comments to mark as spam
     * @param   int     $status  New status (spam by default)
     * @return  bool
     */
    function MarkAs($gadget, $ids, $status = Comments_Info::COMMENTS_STATUS_SPAM)
    {
        if (count($ids) == 0) {
            return true;
        }

        if (!in_array($status, array(Comments_Info::COMMENTS_STATUS_APPROVED, Comments_Info::COMMENTS_STATUS_WAITING,
            Comments_Info::COMMENTS_STATUS_SPAM, Comments_Info::COMMENTS_STATUS_PRIVATE))) {
            $status = Comments_Info::COMMENTS_STATUS_SPAM;
        }

        // Update status...
        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $commentsTable->update(array('status'=>$status))->where('id', $ids, 'in')->exec();

        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $commentsTable->select('gadget', 'reference:integer', 'action');
        $comments = $commentsTable->where('id', $ids, 'in')->fetchAll();
        if (Jaws_Error::IsError($comments)) {
            return $comments;
        }
        foreach($comments as $comment) {
            $this->gadget->event->shout(
                'UpdateComment',
                array($comment['gadget'], $comment['action'], $comment['reference'])
            );
        }

        if ($status == Comments_Info::COMMENTS_STATUS_SPAM) {
            $mPolicy = Jaws_Gadget::getInstance('Policy')->model->loadAdmin('AntiSpam');
            // Submit spam...
            $commentsTable = Jaws_ORM::getInstance()->table('comments');
            $commentsTable->select('id:integer', 'name', 'email', 'url', 'msg_txt', 'msg_txt', 'status:integer');
            $items = $commentsTable->where('id', $ids, 'in')->fetchAll();
            if (Jaws_Error::IsError($items)) {
                return $items;
            }

            foreach ($items as $i) {
                if ($i['status'] != Comments_Info::COMMENTS_STATUS_SPAM) {
                    // FIXME Get $permalink
                    $permalink = '';
                    $mPolicy->SubmitSpam($permalink, $gadget, $i['name'], $i['email'], $i['url'], $i['message']);
                }
            }
        }

        return true;
    }

}