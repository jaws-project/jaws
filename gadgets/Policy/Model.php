<?php
/**
 * Policy Gadget
 *
 * @category   GadgetModel
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class PolicyModel extends Jaws_Gadget_Model
{
    /**
     * Checks wheter the IP is blocked or not
     *
     * @access  public
     * @param   string  $ip IP Address
     * @return  bool    True if the IP is blocked
     */
    function IsIPBlocked($ip)
    {
        $ip_pattern = '/\b(?:\d{1,3}\.){3}\d{1,3}\b/';
        if (preg_match($ip_pattern, $ip)) {
            $ip = ip2long($ip);
            if ($ip < 0) {
                $ip = $ip + 0xffffffff + 1;
            }

            $params = array();
            $params['ip'] = $ip;

            $sql = '
                SELECT [blocked]
                FROM [[policy_ipblock]]
                WHERE {ip} BETWEEN [from_ip] AND [to_ip]';

            $types = array('boolean');
            $blocked = $GLOBALS['db']->queryOne($sql, $params, $types);
            if (!Jaws_Error::IsError($blocked) && !is_null($blocked)) {
                return $blocked;
            }
        }

        return $this->GetRegistry('block_undefined_ip') == 'true';
    }

    /**
     * Checks wheter the Agent is blocked or not
     *
     * @access  public
     * @param   string  $agent  Agent
     * @return  bool    True if the Agent is blocked
     */
    function IsAgentBlocked($agent)
    {
        $xss    = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params = array();
        $params['agent'] = $xss->filter($agent);

        $sql = '
            SELECT [blocked]
            FROM [[policy_agentblock]]
            WHERE [agent] = {agent}';

        $types = array('boolean');
        $blocked = $GLOBALS['db']->queryOne($sql, $params, $types);
        if (!Jaws_Error::IsError($blocked) && !is_null($blocked)) {
            return $blocked;
        }

        return $this->GetRegistry('block_undefined_agent') == 'true';
    }

    /**
     * Load and get captcha
     *
     * @access  public
     * @param   string  $captcha
     * @param   string  $entry
     * @param   string  $description
     * @return  bool    True if captcha loaded successfully
     */
    function LoadCaptcha(&$captcha, &$entry, &$label, &$description)
    {
        $status = $this->GetRegistry('captcha');
        if (($status == 'DISABLED') ||
            ($status == 'ANONYMOUS' && $GLOBALS['app']->Session->Logged())) {
            return false;
        }

        static $objCaptcha;
        if (!isset($objCaptcha)) {
            $objCaptcha = array();
        }

        $dCaptcha = $this->GetRegistry('captcha_driver');
        if (!isset($objCaptcha[$dCaptcha])) {
            require_once JAWS_PATH . 'gadgets/Policy/captchas/' . $dCaptcha . '.php';
            $objCaptcha[$dCaptcha] = new $dCaptcha();
        }

        $resCaptcha = $objCaptcha[$dCaptcha]->Get();
        $captcha = $resCaptcha['captcha']->Get();
        $entry   = empty($resCaptcha['entry'])? null : $resCaptcha['entry']->Get();
        $label   = $resCaptcha['label'];
        $description = $resCaptcha['description'];

        return true;
    }

    /**
     * Load and get captcha
     *
     * @access  public
     * @return  bool    True if captcha loaded successfully
     */
    function CheckCaptcha()
    {
        $status = $this->GetRegistry('captcha');
        if (($status == 'DISABLED') ||
            ($status == 'ANONYMOUS' && $GLOBALS['app']->Session->Logged())) {
            return true;
        }

        static $objCaptcha;
        if (!isset($objCaptcha)) {
            $objCaptcha = array();
        }

        $dCaptcha = $this->GetRegistry('captcha_driver');
        if (!isset($objCaptcha[$dCaptcha])) {
            require_once JAWS_PATH . 'gadgets/Policy/captchas/' . $dCaptcha . '.php';
            $objCaptcha[$dCaptcha] = new $dCaptcha();
        }

        if (!$objCaptcha[$dCaptcha]->Check()) {
            return Jaws_Error::raiseError(_t('GLOBAL_CAPTCHA_ERROR_DOES_NOT_MATCH'),
                                          'Jaws_Captcha',
                                          JAWS_ERROR_NOTICE);
        }

        return true;
    }

    /**
     * Is spam?
     *
     * @access  public
     * @param   string  $permalink
     * @param   string  $type
     * @param   string  $author
     * @param   string  $author_email
     * @param   string  $author_url
     * @param   string  $content
     * @return  bool    True if spam otherwise false
     */
    function IsSpam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        $filter = preg_replace('/[^[:alnum:]_-]/', '', $this->GetRegistry('filter'));
        if ($filter == 'DISABLED' || !@include_once(JAWS_PATH . "gadgets/Policy/filters/$filter.php"))
        {
            return false;
        }

        static $objFilter;
        if (!isset($objFilter)) {
            $objFilter = new $filter();
        }

        return $objFilter->IsSpam($permalink, $type, $author, $author_email, $author_url, $content);
    }

}