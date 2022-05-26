<?php
/**
 * Shoutbox Gadget
 *
 * @category   GadgetModel
 * @package    Shoutbox
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_Model_Comment extends Jaws_Gadget_Model
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
        $subject  = $this::t('COMMENT_MAIL_TITLE');
        $comment .= "<br /><br />";
        $comment .= $this::t("COMMENT_MAIL_VISIT_URL", $link. '/', $this->gadget->registry->fetch('site_name', 'Settings'));

        $mail = Jaws_Mail::getInstance();
        $mail->SetFrom($from_email);
        $mail->AddRecipient('');
        $mail->SetSubject($subject);
        $mail->SetBody($comment, array('format' => 'html'));
        $mail->send();
    }
}