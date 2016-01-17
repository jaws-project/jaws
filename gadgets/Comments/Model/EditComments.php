<?php
/**
 * Comments Model
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Model_EditComments extends Jaws_Gadget_Model
{
    /**
     * Message is unique? Is it not duplicated?
     *
     * @access  public
     * @param   string   $hash  Message hash (crc)
     * @return  bool    Exists (true) or Not Exists (false)
     */
    function IsMessageDuplicated($hash)
    {
        $commentsTable = Jaws_ORM::getInstance()->table('comments_details');
        $howMany = $commentsTable->select('count([id]):integer')->where('hash', $hash)->fetchOne();

        ///FIXME check for errors
        return ($howMany == '0') ? false : true;
    }

    /**
     * Inserts a new comment
     *
     * @param   string  $gadget     Gadget's name
     * @param   string  $action     Gadget's action name
     * @param   int     $reference  Gadget's reference id.
     *                              It can be the ID of a blog entry, the ID of a
     *                              photo in Phoo, etc. This needs to be a reference
     *                              to find the comments related to a specific record
     *                              in a gadget.
     * @param   string  $name       Author's name
     * @param   string  $email      Author's email
     * @param   string  $url        Author's url
     * @param   string  $message    Author's message
     * @param   string  $permalink  Permanent link to resource
     * @param   int     $status
     * @param   boolean $private    Is a private message?
     * @return  int     Comment status or Jaws_Error on any error
     * @access  public
     */
    function InsertComment($gadget, $action, $reference, $name, $email, $url, $message,
                          $permalink, $status = Comments_Info::COMMENTS_STATUS_APPROVED, $private = null)
    {
        if (!in_array($status, array(Comments_Info::COMMENTS_STATUS_APPROVED, Comments_Info::COMMENTS_STATUS_WAITING,
            Comments_Info::COMMENTS_STATUS_SPAM, Comments_Info::COMMENTS_STATUS_PRIVATE))) {
            $status = Comments_Info::COMMENTS_STATUS_SPAM;
        }

        if ($private) {
            $status = Comments_Info::COMMENTS_STATUS_PRIVATE;
        }

        $messageHash = crc32($message);
        if ($this->gadget->registry->fetch('allow_duplicate') == 'no') {
            if ($this->IsMessageDuplicated($messageHash)) {
                return new Jaws_Error(_t('COMMENTS_SPAM_POSSIBLE_DUPLICATE_MESSAGE'));
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

        $objORM = Jaws_ORM::getInstance();
        $objORM->beginTransaction();

        // insert comment
        $cData = array();
        $cData['gadget']        = $gadget;
        $cData['action']        = $action;
        $cData['reference']     = $reference;
        $cData['insert_time']   = time();
        $cTable = $objORM->table('comments');
        $cid = $cTable->insert($cData)->exec();
        if (Jaws_Error::IsError($cid)) {
            return $cid;
        }

        // insert comment's details
        $uip = bin2hex(inet_pton($_SERVER['REMOTE_ADDR']));
        $cData = array();
        $cData['cid']           = $cid;
        $cData['user']          = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $cData['name']          = $name;
        $cData['email']         = $email;
        $cData['url']           = $url;
        $cData['uip']           = $uip;
        $cData['msg_txt']       = $message;
        $cData['hash']          = $messageHash;
        $cData['status']        = (int)$status;
        $cData['insert_time']   = time();

        $commentsTable = $objORM->table('comments_details');
        $res = $commentsTable->insert($cData)->exec();

        //commit transaction
        $objORM->commit();
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
     * @return  bool    True if success or Jaws_Error on any error
     * @access  public
     */
    function UpdateComment($gadget, $id, $name, $email, $url, $message, $reply, $permalink, $status)
    {
        $cData = array();
        $cData['name']    = $name;
        $cData['email']   = $email;
        $cData['url']     = $url;
        $cData['msg_txt'] = $message;
        $cData['hash']    = crc32($message);
        $cData['replier'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $cData['status']  = $status;
        if ($this->gadget->GetPermission('ReplyComments')) {
            $cData['reply'] = $reply;
        }

        $commentsTable = Jaws_ORM::getInstance()->table('comments_details');
        $result = $commentsTable->update($cData)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (($status == Comments_Info::COMMENTS_STATUS_SPAM || $cData['status'] == Comments_Info::COMMENTS_STATUS_SPAM) &&
            $cData['status'] != $status
        ) {
            $mPolicy = Jaws_Gadget::getInstance('Policy')->model->loadAdmin('AntiSpam');
            if ($status == Comments_Info::COMMENTS_STATUS_SPAM) {
                $mPolicy->SubmitSpam($permalink, $gadget, $name, $email, $url, $message);
            } else {
                $mPolicy->SubmitHam($permalink, $gadget, $name, $email, $url, $message);
            }
        }

        return true;
    }

    /**
     * Mark as a different status several comments
     *
     * @access  public
     * @param   array   $ids     Id's of the comments to mark as spam
     * @param   int     $status  New status (spam by default)
     * @return  bool
     */
    function MarkAs($ids, $status = Comments_Info::COMMENTS_STATUS_SPAM)
    {
        if (count($ids) == 0) {
            return true;
        }

        if (!in_array($status, array(Comments_Info::COMMENTS_STATUS_APPROVED, Comments_Info::COMMENTS_STATUS_WAITING,
            Comments_Info::COMMENTS_STATUS_SPAM, Comments_Info::COMMENTS_STATUS_PRIVATE))) {
            $status = Comments_Info::COMMENTS_STATUS_SPAM;
        }

        // Update status...
        $commentsTable = Jaws_ORM::getInstance()->table('comments_details');
        $commentsTable->update(array('status'=>$status))->where('id', $ids, 'in')->exec();

        if ($status == Comments_Info::COMMENTS_STATUS_SPAM) {
            $mPolicy = Jaws_Gadget::getInstance('Policy')->model->loadAdmin('AntiSpam');
            // Submit spam...
            $commentsTable = Jaws_ORM::getInstance()->table('comments_details');
            $commentsTable->select('comments.gadget', 'name', 'email', 'url', 'msg_txt', 'status:integer');
            $commentsTable->join('comments', 'comments.id', 'comments_details.cid');
            $items = $commentsTable->where('comments_details.id', $ids, 'in')->fetchAll();
            if (Jaws_Error::IsError($items)) {
                return $items;
            }

            foreach ($items as $i) {
                if ($i['status'] != Comments_Info::COMMENTS_STATUS_SPAM) {
                    // FIXME Get $permalink
                    $permalink = '';
                    $mPolicy->SubmitSpam($permalink, $i['gadget'], $i['name'], $i['email'], $i['url'], $i['msg_txt']);
                }
            }
        }

        return true;
    }

}