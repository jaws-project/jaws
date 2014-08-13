<?php
/**
 * Class that deals like a wrapper between Jaws and pear/Mail
 *
 * @category   Mail
 * @package    Core
 * @author     David Coallier <davidc@agoraproduction.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Mail
{
    /**
     * The mailer type
     * @param   string $mailer The mailer type
     */
    var $mailer = '';

    /**
     * Send email via this email
     * @param   string $gate_email The default site from email address
     */
    var $gate_email = '';

    /**
     * From name
     * @param   string $gate_title The default site from email name
     */
    var $gate_title = '';

    /**
     * default site email address
     * @param   string $site_email The default site email address
     */
    var $site_email = '';

    /**
     * site email name
     * @param   string $site_name The default site email name
     */
    var $site_name = '';

    /**
     * SMTP email verification?
     * @param   bool    $smtp_vrfy SMTP email verification?
     */
    var $smtp_vrfy = false;

    // {{{ Variables
    /**
     * The server infos (host,login,pass)
     * @param   array $server The server infos
     */
    var $params = array();

    /**
     * The email recipients.
     * @param   array $recipients The recipients.
     */
    var $recipient = array();

    /**
     * The email headers
     *
     * @param   array string $headers The headers of the mail.
     */
    var $headers = array();

    /**
     * The crlf character(s)
     *
     * @param   string $crlf
     */
    var $crlf = "\n";

    /**
     * A object of Mail_Mime
     *
     * @param object $mail_mime
     */
    var $mail_mime;

    /**
     * Blocked domains
     *
     * @access  private
     * @param   string  $blocked_domains
     */
    private $blocked_domains;

    /**
     * This creates the mail object that will
     * add recipient, send emails to destination
     * email addresses calling functions.
     *
     * @access constructor
     */
    function Jaws_Mail($init = true)
    {
        require_once PEAR_PATH. 'Mail.php';
        require_once PEAR_PATH. 'Mail/mime.php';
        $this->mail_mime = new Mail_Mime($this->crlf);
        $this->headers['Subject'] = '';
        if ($init) {
            $this->Init();
        }
    }

    /**
     * This function loads the mail settings from
     * the registry.
     *
     * @access  public
     */
    function Init()
    {
        if (!isset($GLOBALS['app'])) {
            return new Jaws_Error('$GLOBALS[\'app\'] not available',
                                  __FUNCTION__);
        }

        // Get blocked domains name from registry
        $this->blocked_domains = $GLOBALS['app']->Registry->fetch('blocked_domains', 'Policy');

        // Get the mail settings data from registry
        $this->mailer     = $GLOBALS['app']->Registry->fetch('mailer', 'Settings');
        $this->gate_email = $GLOBALS['app']->Registry->fetch('gate_email', 'Settings');
        $this->gate_title = $GLOBALS['app']->Registry->fetch('gate_title', 'Settings');
        $this->smtp_vrfy  = $GLOBALS['app']->Registry->fetch('smtp_vrfy', 'Settings') == 'true';

        $this->site_email = $GLOBALS['app']->Registry->fetch('site_email', 'Settings');
        $this->site_name  = $GLOBALS['app']->Registry->fetch('site_name', 'Settings');

        $params = array();
        $params['sendmail_path'] = $GLOBALS['app']->Registry->fetch('sendmail_path', 'Settings');
        $params['sendmail_args'] = $GLOBALS['app']->Registry->fetch('sendmail_args', 'Settings');
        $params['host']          = $GLOBALS['app']->Registry->fetch('smtp_host', 'Settings');
        $params['port']          = $GLOBALS['app']->Registry->fetch('smtp_port', 'Settings');
        $params['auth']          = $GLOBALS['app']->Registry->fetch('smtp_auth', 'Settings')  == 'true';
        $params['pipelining']    = $GLOBALS['app']->Registry->fetch('pipelining', 'Settings') == 'true';
        $params['username']      = $GLOBALS['app']->Registry->fetch('smtp_user', 'Settings');
        $params['password']      = $GLOBALS['app']->Registry->fetch('smtp_pass', 'Settings');

        $this->params = $params;
        return $this->params;
    }

    /**
     * This adds a recipient to the mail to send.
     *
     * @access  public
     * @param   string  $recipients    The recipients to add.
     * @param   string  $inform_type   Inform type(To, Bcc, Cc)
     * @return  bool    True
     */
    function AddRecipient($recipients = '', $inform_type = 'To')
    {
        $valid_recipients = array();
        $recipients = array_filter(array_map('Jaws_UTF8::trim', explode(',', $recipients)));
        foreach ($recipients as $key => $recipient) {
            if (false !== $ltPos = Jaws_UTF8::strpos($recipient, '<')) {
                $ename = Jaws_UTF8::encode_mimeheader(Jaws_UTF8::substr($recipient, 0, $ltPos));
                $email = Jaws_UTF8::substr($recipient, $ltPos + 1, -1);
                $recipients[$key] = $ename. "<$email>";
            } else {
                $ename = '';
                $email = $recipient;
                $recipients[$key] =  $email;
            }

            // check blocked domains
            if (false !== strpos($this->blocked_domains, "\n".substr(strrchr($email, '@'), 1))) {
                continue;
            }

            $valid_recipients[] = $recipients[$key];
        }

        if (empty($valid_recipients)) {
            if (!empty($this->site_name)) {
                $valid_recipients[] = Jaws_UTF8::encode_mimeheader($this->site_name) . ' <'. $this->site_email. '>';
            } else {
                $valid_recipients[] = $this->site_email;
            }
        }

        switch (strtolower($inform_type)) {
            case 'to':
                $this->headers['To'] =
                    (array_key_exists('To', $this->headers)? ($this->headers['To']. ',') : '').
                    implode(',', $valid_recipients);
                break;
            case 'cc':
                $this->headers['Cc'] =
                    (array_key_exists('Cc', $this->headers)? ($this->headers['Cc']. ',') : '').
                    implode(',', $valid_recipients);
                break;
        }

        $this->recipient = array_merge($this->recipient, $valid_recipients);
        return true;
    }

    /**
     * This function sets the subject of the email to send.
     *
     * @param   string $subject       Subject of the email.
     * @access  public
     * @return  void
     */
    function SetSubject($subject = '')
    {
        $this->headers['Subject'] = $subject;
    }

    /**
     * This function sets the from of the email to send.
     *
     * @param   string $from_email    Who the email is from(E-mail address).
     * @param   string $from_name     Who the email is from(name).
     * @access  public
     * @return  void
     */
    function SetFrom($from_email = '', $from_name = '')
    {
        if ($this->smtp_vrfy) {
            $replyTo    = $from_name . ' <'.$from_email.'>';
            $from_name  = $this->gate_title;
            $from_email = $this->gate_email;
        } else {
            $from_name  = empty($from_email)? $this->gate_title : $from_name;
            $from_email = empty($from_email)? $this->gate_email : $from_email;
        }

        $this->headers['From'] = $from_name . ' <'.$from_email.'>';
        $this->headers['Reply-To'] = isset($replyTo)? $replyTo : $this->headers['From'];
    }

    /**
     * This function sets the body, the structure
     * of the email, what's in it..
     *
     * @param   string $body   The body of the email
     * @param   string $format The format to use.
     * @access  protected
     * @return  string $body
     */
    function SetBody($body, $format = 'html')
    {
        if (!isset($body) && empty($body)) {
            return false;
        }

        switch ($format) {
            case 'file':
                $res = $this->mail_mime->addAttachment($body);
                break;
            case 'image':
                $res = $this->mail_mime->addHTMLImage($body);
                break;
            case 'html':
                $res = $this->mail_mime->setHTMLBody($body);
                break;
            case 'text':
                $res = $this->mail_mime->setTXTBody($body);
                break;
            default:
                $res = false;
        }

        return $res;
    }

    /**
     * This function sends the email
     *
     * @access  public
     * @return  mixed
     */
    function send()
    {
        $mail = null;
        switch ($this->mailer) {
            case 'phpmail':
                $mail = Mail::factory('mail');
                break;
            case 'sendmail':
                $mail = Mail::factory('sendmail', $this->params);
                break;
            case 'smtp':
                $mail = Mail::factory('smtp', $this->params);
                break;
            default:
                return false;
        }

        $realbody = $this->mail_mime->get(
            array(
                'html_encoding' => '8bit',
                'text_encoding' => '8bit',
                'head_encoding' => 'base64',
                'html_charset'  => 'utf-8',
                'text_charset'  => 'utf-8',
                'head_charset'  => 'utf-8',
            )
        );
        if (empty($this->recipient)) {
            $this->AddRecipient();
        }

        $headers  = $this->mail_mime->headers($this->headers);
        $res = $mail->send($this->recipient, $headers, $realbody);
        if (PEAR::isError($res)) {
            return new Jaws_Error($res->getMessage(),
                                  __FUNCTION__);
        }

        return true;
    }

    /**
     * Resets the values and updates
     *
     * @access  public
     */
    function ResetValues()
    {
        $this->headers = array();
        $this->headers['Subject'] = '';

        $this->recipient = array();
        unset($this->mail_mime);
        $this->mail_mime = new Mail_Mime($this->crlf);
    }

}