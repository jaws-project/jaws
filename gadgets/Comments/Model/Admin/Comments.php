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
class Comments_Model_Admin_Comments extends Jaws_Gadget_Model
{
    /**
     * Message is unique? Is it not duplicated?
     *
     * @access  private
     * @param   int     $commentReference   Gadget_Action_Reference Id
     * @param   int     $user               User
     * @param   string  $hash               Message hash (crc)
     * @return  bool    Exists (true) or Not Exists (false)
     */
    function IsMessageDuplicated($commentReference, $user, $hash)
    {
        return Jaws_ORM::getInstance()
            ->table('comments_details')
            ->select('count(id):integer')
            ->where('cid', $commentReference)
            ->and()
            ->where('user', $user)
            ->and()
            ->where('hash', $hash)
            ->fetchOne();
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
        // find comment reference id
        $commentReference = $objORM->table('comments')
            ->igsert(array(
                'gadget'      => $gadget,
                'action'      => $action,
                'reference'   => $reference,
                'last_update' => time(),
            ))
            ->where('gadget', $gadget)->and()
            ->where('action', $action)->and()
            ->where('reference', $reference)
            ->exec();
        if (Jaws_Error::IsError($commentReference)) {
            return $commentReference;
        }

        // check duplicated message
        $hash = crc32($message);
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $duplicated = $this->IsMessageDuplicated($commentReference, $user, $hash);
        if (Jaws_Error::IsError($duplicated)) {
            return $duplicated;
        } elseif ($duplicated) {
            return Jaws_Error::raiseError(
                _t('COMMENTS_SPAM_POSSIBLE_DUPLICATE_MESSAGE'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // begin transaction
        $objORM->beginTransaction();

        // insert comment's details
        $ret = $objORM->table('comments_details')->insert(
            array(
                'cid'         => $commentReference,
                'user'        => $user,
                'name'        => $name,
                'email'       => $email,
                'url'         => $url,
                'uip'         => bin2hex(inet_pton($_SERVER['REMOTE_ADDR'])),
                'msg_txt'     => $message,
                'hash'        => $hash,
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
                    ->select('count(id)')->where('cid', $commentReference),
                'last_update' => time()
            )
        )->where('id', $commentReference)
        ->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        //commit transaction
        $objORM->commit();

        // shout SiteActivity event
        $this->gadget->event->shout('SiteActivity', array('action'=>'NewComment'));

        return $ret;
    }


    /**
     * Updates a comment
     *
     * @param   string  $gadget     Gadget's name
     * @param   int     $cid        Comment's ID
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
    function UpdateComment($gadget, $cid, $name, $email, $url, $message, $reply, $permalink, $status)
    {
        $cData = array();
        $cData['name']    = $name;
        $cData['email']   = $email;
        $cData['url']     = $url;
        $cData['msg_txt'] = $message;
        $cData['hash']    = crc32($message);
        $cData['status']  = $status;
        if ($this->gadget->GetPermission('ReplyComments')) {
            $cData['reply'] = $reply;
            $cData['replier'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
        }

        $commentsTable = Jaws_ORM::getInstance()->table('comments_details');
        $result = $commentsTable->update($cData)->where('id', $cid)->exec();
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


    /**
     * Deletes a comment
     *
     * @access  public
     * @param   int     $id    Comment ID
     * @return  bool    True if success or Jaws_Error on any error
     */
    function Delete($id)
    {
        $objORM = Jaws_ORM::getInstance();

        // find comment reference id
        $gar = $objORM->table('comments_details')->select('cid:integer')->where('id', $id)->fetchOne();
        if (Jaws_Error::IsError($gar)) {
            return $gar;
        }

        if (empty($gar)) {
            return Jaws_Error::raiseError(
                _t('COMMENTS_REFERENCE_NOTFOUND'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        // begin transaction
        $objORM->beginTransaction();

        $ret = $objORM->table('comments_details')->delete()->where('id', $id)->exec();
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
        )->where('id', $gar)
        ->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        //commit transaction
        $objORM->commit();

        return $ret;
    }


    /**
     * Deletes all comments of a certain gadget/reference
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   int     $reference  Gadget reference id
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function DeleteGadgetComments($gadget, $reference = '')
    {
        $objORM = Jaws_ORM::getInstance();
        // begin transaction
        $objORM->beginTransaction();

        // delete comments in slave table
        $objORM->table('comments_details')
            ->delete()
            ->using('comments')
            ->where('comments_details.cid', $objORM->expr('comments.id'))
            ->and()
            ->where('comments.gadget', $gadget);
        if (!empty($reference)) {
            $objORM->and()->where('comments.reference', (int)$reference);
        }
        $ret = $objORM->exec();
        if (Jaws_Error::IsError($ret)) {
            return $ret;
        }

        // delete comments references in master table
        $objORM->table('comments')
            ->delete()
            ->where('gadget', $gadget);
        if (!empty($reference)) {
            $objORM->and()->where('reference', (int)$reference);
        }
        $res = $objORM->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        //commit transaction
        $objORM->commit();

        return $ret;
    }


    /**
     * Does a massive comment delete
     *
     * @access  public
     * @param   array   $ids  Ids of comments
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function DeleteMassiveComment($ids)
    {
        if (!is_array($ids)) {
            $ids = func_get_args();
        }

        foreach ($ids as $id) {
            $res = $this->Delete($id);
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
        }

        return true;
    }

}