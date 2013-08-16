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

            $table = Jaws_ORM::getInstance()->table('policy_ipblock');
            $table->select('blocked:boolean');
            $table->where(
                array($ip, 'integer'),
                array($table->expr('from_ip'),
                $table->expr('to_ip')),
                'between'
            );
            $blocked = $table->fetchOne();
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
        $table = Jaws_ORM::getInstance()->table('policy_agentblock');
        $table->select('blocked:boolean');
        $table->where('agent', Jaws_XSS::filter($agent));
        $blocked = $table->fetchOne();
        if (!Jaws_Error::IsError($blocked) && !is_null($blocked)) {
            return $blocked;
        }

        return $this->gadget->registry->fetch('block_undefined_agent') == 'true';
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