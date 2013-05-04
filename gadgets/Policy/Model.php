<?php
/**
 * Policy Gadget
 *
 * @category   GadgetModel
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Model extends Jaws_Gadget_Model
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

        return $this->gadget->registry->fetch('block_undefined_ip') == 'true';
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
        $params = array();
        $params['agent'] = Jaws_XSS::filter($agent);

        $sql = '
            SELECT [blocked]
            FROM [[policy_agentblock]]
            WHERE [agent] = {agent}';

        $types = array('boolean');
        $blocked = $GLOBALS['db']->queryOne($sql, $params, $types);
        if (!Jaws_Error::IsError($blocked) && !is_null($blocked)) {
            return $blocked;
        }

        return $this->gadget->registry->fetch('block_undefined_agent') == 'true';
    }

    /**
     * Load and get captcha
     *
     * @access  public
     * @param   string  $field
     * @return  bool    True if captcha loaded successfully
     */
    function LoadCaptcha($field = 'default')
    {
        if (!extension_loaded('gd')) {
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, 'LoadCaptcha error: GD extension not loaded');
            return false;
        }

        $status = $this->gadget->registry->fetch($field. '_captcha_status');
        switch ($field) {
            case 'login':
                $bad_logins = (int)$GLOBALS['app']->Session->GetAttribute('bad_login_count');
                if (($status == 'DISABLED') || ($bad_logins < (int)$status)) {
                    return false;
                }
                break;

            default:
                if (($status == 'DISABLED') ||
                    ($status == 'ANONYMOUS' && $GLOBALS['app']->Session->Logged())) {
                    return false;
                }
        }

        $dCaptcha = $this->gadget->registry->fetch($field. '_captcha_driver');
        $objCaptcha =& Jaws_Captcha::getInstance($dCaptcha, $field);

        $resCaptcha = $objCaptcha->get();
        $resCaptcha['key']     = empty($resCaptcha['key'])? null : $resCaptcha['key']->Get();
        $resCaptcha['label']   = empty($resCaptcha['label'])? null : $resCaptcha['label']->Get();
        $resCaptcha['entry']   = empty($resCaptcha['entry'])? null : $resCaptcha['entry']->Get();
        $resCaptcha['captcha'] = $resCaptcha['captcha']->Get();
        return $resCaptcha;
    }

    /**
     * Load and get captcha
     *
     * @access  public
     * @param   string  $field
     * @return  bool    True if captcha loaded successfully
     */
    function CheckCaptcha($field = 'default')
    {
        $status = $this->gadget->registry->fetch($field. '_captcha_status');
        switch ($field) {
            case 'login':
                $bad_logins = (int)$GLOBALS['app']->Session->GetAttribute('bad_login_count');
                if (($status == 'DISABLED') || ($bad_logins < (int)$status)) {
                    return true;
                }
                break;

            default:
                if (($status == 'DISABLED') ||
                    ($status == 'ANONYMOUS' && $GLOBALS['app']->Session->Logged())) {
                    return true;
                }
        }

        $dCaptcha = $this->gadget->registry->fetch($field. '_captcha_driver');
        $objCaptcha =& Jaws_Captcha::getInstance($dCaptcha, $field);
        if (!$objCaptcha->check()) {
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
        $filter = preg_replace('/[^[:alnum:]_-]/', '', $this->gadget->registry->fetch('filter'));
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