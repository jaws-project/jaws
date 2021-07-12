<?php
/**
 * Policy Gadget
 *
 * @category   GadgetModel
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Model_IP extends Jaws_Gadget_Model
{
    /**
     * Checks wheter the IP is blocked or not
     *
     * @access  public
     * @param   string  $ip     IP Address
     * @param   string  $script JAWS_SCRIPT
     * @param   string  $gadget Gadget name
     * @param   string  $action Action name
     * @return  bool    True if request(script/gadget/action) accessible
     */
    function IsReguestAccessible($ip, $script, $gadget, $action)
    {
        $result = Jaws_ORM::getInstance()
            ->table('policy_zone_action')
            ->select('access:boolean')
            ->join('policy_zone_range', 'policy_zone_range.zone', 'policy_zone_action.zone')
            ->where('policy_zone_range.from', $ip, '>=')
            ->and()
            ->where('policy_zone_range.to', $ip, '<=')
            ->and()
            ->openWhere('script', $script)
            ->or()
            ->closeWhere('script', 0)
            ->and()
            ->openWhere('gadget', $gadget)
            ->or()
            ->closeWhere('gadget', '')
            ->and()
            ->openWhere('action', $action)
            ->or()
            ->closeWhere('action', '')
            ->orderBy('policy_zone_action.order')
            ->fetchOne();
        return $result;
    }

    /**
     * Checks wheter the IP is blocked or not
     *
     * @access  public
     * @param   string  $ip     IP Address
     * @param   string  $script JAWS_SCRIPT
     * @return  bool    True if the IP is blocked
     */
    function IsIPBlocked($ip, $script)
    {
        $ip_pattern = '/\b(?:\d{1,3}\.){3}\d{1,3}\b/';
        if (preg_match($ip_pattern, $ip)) {
            $ip = ip2long($ip);
            if ($ip < 0) {
                $ip = $ip + 0xffffffff + 1;
            }

            $table = Jaws_ORM::getInstance()->table('policy_ipblock');
            $table->select('blocked:boolean');
            $blocked = $table->where(
                    array($ip, 'integer'),
                    array($table->expr('from_ip'),
                        $table->expr('to_ip')),
                    'between'
                )
                ->and()
                ->openWhere('script', $script)
                ->or()
                ->closeWhere('script', null, 'is null')
                ->orderBy('order asc', 'id desc')
                ->fetchOne();
            if (!Jaws_Error::IsError($blocked) && !is_null($blocked)) {
                return $blocked;
            }
        }

        return $this->gadget->registry->fetch('block_undefined_ip') == 'true';
    }
}