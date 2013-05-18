<?php
define('COMMENT_STATUS_APPROVED',    1);
define('COMMENT_STATUS_WAITING',     2);
define('COMMENT_STATUS_SPAM',        3);

/**
 * Comments Gadget
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Model extends Jaws_Gadget_Model
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
        $howmany = $commentsTable->select('count([id]):integer')->where('msg_key', $md5)->getOne();

        ///FIXME check for errors
        return ($howmany == '0') ? false : true;
    }

    /**
     * Adds a new comment
     *
     * @param   string  $gadget   Gadget's name
     * @param   int     $gadgetId  Gadget's reference id.
     *                             It can be the ID of a blog entry, the ID of a
     *                             photo in Phoo, etc. This needs to be a reference
     *                             to find the comments related to a specific record
     *                             in a gadget.
     * @param   string  $action
     * @param   string  $name      Author's name
     * @param   string  $email     Author's email
     * @param   string  $url       Author's url
     * @param   string  $message   Author's message
     * @param   string  $ip        Author's IP
     * @param   string  $permalink Permanent link to resource
     * @param   int     $status
     * @return  int     Comment status or Jaws_Error on any error
     * @access  public
     */
    function NewComment($gadget, $gadgetId, $action, $name, $email, $url, $message,
                        $ip, $permalink, $status = COMMENT_STATUS_APPROVED)
    {
        if (!in_array((int)$status, array(COMMENT_STATUS_APPROVED, COMMENT_STATUS_WAITING, COMMENT_STATUS_SPAM))) {
            $status = COMMENT_STATUS_SPAM;
        }

        $message_key = md5($message);
        if ($this->gadget->registry->fetch('allow_duplicate') == 'no') {
            if ($this->IsMessageDuplicated($message_key)) {
                return new Jaws_Error(_t('GLOBAL_SPAM_POSSIBLE_DUPLICATE_MESSAGE'), _t('COMMENTS_NAME'));
            }
        }

        // Validate website url
        if (!preg_match('$^(http|https|ftp)://([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?/?$i', $url)) {
            $url = '';
        }

        // Comment Status...
        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        if ($mPolicy->IsSpam($permalink, $gadget, $name, $email, $url, $message)) {
            $status = COMMENT_STATUS_SPAM;
        }

        $cData = array();
        $cData['reference']     = $gadgetId;
        $cData['action']        = $action;
        $cData['gadget']        = $gadget;
        $cData['name']          = $name;
        $cData['email']         = $email;
        $cData['url']           = $url;
        $cData['msg_txt']       = $message;
        $cData['status']        = $status;
        $cData['msg_key']       = $message_key;
        $cData['ip']            = $ip;
        $cData['user']          = $GLOBALS['app']->Session->GetAttribute('user');
        $cData['createtime']    = $GLOBALS['db']->Date();

        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $result = $commentsTable->insert($cData)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('COMMENTS_NAME'));
        }

        return $status;
    }

}