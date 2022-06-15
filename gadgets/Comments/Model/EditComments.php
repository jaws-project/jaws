<?php
/**
 * Comments Model
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2022 Jaws Development Group
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
     * @param   int     $reference  Gadget's reference id
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
                return new Jaws_Error($this::t('SPAM_POSSIBLE_DUPLICATE_MESSAGE'));
            }
        }

        // Validate website url
        if (!preg_match('$^(http|https|ftp)://([A-Z0-9][A-Z0-9\-_]*(?:.[A-Z0-9][A-Z0-9\-_]*)+):?(d+)?/?$i', $url)) {
            $url = '';
        }

        // Comment Status...
        $mPolicy = Jaws_Gadget::getInstance('Policy')->model->load('AntiSpam');
        if ($mPolicy->IsSpam($permalink, $gadget, $name, $email, $url, $message)) {
            $status = COMMENTS_STATUS_SPAM;
        }

        $objORM = Jaws_ORM::getInstance();
        // find master comment id
        $gar = $objORM->table('comments')
            ->select('id:integer')
            ->where('gadget', $gadget)->and()
            ->where('action', $action)->and()
            ->where('reference', $reference)
            ->fetchOne();
        if (Jaws_Error::IsError($gar)) {
            return $gar;
        }

        // begin transaction
        $objORM->beginTransaction();

        // insert comment's details
        $ret = $objORM->table('comments_details')->insert(
            array(
                'cid'         => $gar,
                'user'        => (int)$this->app->session->user->id,
                'name'        => $name,
                'email'       => $email,
                'url'         => $url,
                'uip'         => bin2hex(inet_pton($_SERVER['REMOTE_ADDR'])),
                'msg_txt'     => $message,
                'hash'        => $messageHash,
                'status'      => (int)$status,
                'insert_time' => time()
            )
        )->exec();
        if (Jaws_Error::IsError($ret)) {
            return $ret;
        }

        // update comments count
        $res = $objORM->table('comments')->update(
            array(
                'comments_count' => Jaws_ORM::getInstance()->table('comments_details')
                    ->select('count(id)')->where('cid', $gar),
                'last_update' => time()
            )
        )->where('gadget', $gadget)->and()
        ->where('action', $action)->and()
        ->where('reference', $reference)
        ->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        //commit transaction
        $objORM->commit();
        return $ret;
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
        $cData['replier'] = (int)$this->app->session->user->id;
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