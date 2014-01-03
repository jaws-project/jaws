<?php
/**
 * Policy Gadget
 *
 * @category   GadgetModel
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Model_IP extends Jaws_Gadget_Model
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
}