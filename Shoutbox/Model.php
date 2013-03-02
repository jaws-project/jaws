<?php
/**
 * Shoutbox Gadget
 *
 * @category   GadgetModel
 * @package    Shoutbox
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_Model extends Jaws_Gadget_Model
{
    /**
     * This function mails the comments to the owner
     *
     * @access  public
     * @param   int     $link           The permanent link
     * @param   string  $from_email     The email to sendto
     * @param   string  $comment        The body of the email (The actual comment)
     * @param   string  $url            The actual comment url
     */
    function MailComment($link, $from_email, $comment, $url)
    {
        require_once JAWS_PATH . '/include/Jaws/Mail.php';
        $mail = new Jaws_Mail;

        $subject  = _t('SHOUTBOX_COMMENT_MAIL_TITLE');
        $comment .= "<br /><br />";
        $comment .= _t("SHOUTBOX_COMMENT_MAIL_VISIT_URL", $link. '/', $this->gadget->GetRegistry('site_name', 'Settings'));

        $mail->SetFrom($from_email);
        $mail->AddRecipient('');
        $mail->SetSubject($subject);
        $mail->SetBody($comment, 'html');
        $result = $mail->send();
    }

    /**
     * Create a new entry in the shoutbox table
     *
     * @access  public
     * @param   string  $name           Who is posting?
     * @param   string  $message        The message of the post
     * @param   string  $email          Email of the poster
     * @param   string  $url            Url of the poster
     * @param   string  $ip             IP of the poster
     * @param   bool    $set_cookie     True if we should create a cookie or false if not.
     * @return  bool    True if query is successful and Jaws_Error on any error
     */
    function NewEntry($name, $message, $email = '', $url = '', $ip = '', $set_cookie = true)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model');

        ///FIXME: Lets get a better ip detection ;)
        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $permalink = $GLOBALS['app']->GetSiteURL();
        $max_strlen = (int)$this->gadget->GetRegistry('max_strlen');
        if ($GLOBALS['app']->UTF8->strlen($message) > $max_strlen) {
            $message = $GLOBALS['app']->UTF8->substr($message, 0, $max_strlen - 3).'...';
        }

        $status = $this->gadget->GetRegistry('comment_status');
        if ($GLOBALS['app']->Session->GetPermission('Shoutbox', 'ManageComments')) {
            $status = COMMENT_STATUS_APPROVED;
        }

        $res = $cModel->NewComment(
            $this->gadget->name, 0, '', $name, $email,
            $url, $message, $ip, $permalink, 0, $status
        );
        if (Jaws_Error::isError($res)) {
            return new Jaws_Error($res->getMessage(), _t('SHOUTBOX_NAME'));
        }

        //Send an email to website owner
        $this->MailComment($permalink, $email, $message, $url);

        if ($set_cookie) {
            $GLOBALS['app']->Session->SetCookie('visitor_name',  $name,  60*24*150);
            $GLOBALS['app']->Session->SetCookie('visitor_email', $email, 60*24*150);
            $GLOBALS['app']->Session->SetCookie('visitor_url',   $url,   60*24*150);
        }

        return true;
    }

    /**
     * Get last entries delimited by a given limit
     *
     * @access  public
     * @param   int     $limit      Entries limit
     * @return  array   Array with last N entries
     */
    function GetEntries($limit = 10)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model');
        $entries = $cModel->GetRecentComments($this->gadget->name, $limit, true, false, false, true);
        return $entries;
    }

}