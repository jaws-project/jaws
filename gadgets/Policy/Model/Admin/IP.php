<?php
/**
 * Policy Gadget Admin
 *
 * @category   GadgetModel
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Model_Admin_IP extends Jaws_Gadget_Model
{
    /**
     * Get blocked IP range
     *
     * @access  public
     * @param   int     $id ID of the to-be-blocked IP range addresses
     * @return  array IP range info or Jaws_Error on failure
     */
    function GetIPRange($id)
    {
        $table = Jaws_ORM::getInstance()->table('policy_ipblock');
        $table->select('id', 'from_ip', 'to_ip', 'blocked:boolean');
        return $table->where('id', (int)$id)->fetchRow();
    }

    /**
     * Returns total of blocked IPs
     *
     * @access  public
     * @return  DB resource
     */
    function GetTotalOfBlockedIPs()
    {
        $table = Jaws_ORM::getInstance()->table('policy_ipblock');
        $table->select('COUNT(id)');
        return $table->fetchOne();
    }

    /**
     * Retrive all blocked IPs
     *
     * @access  public
     * @param   mixed   $limit  Limit of data to retrieve (false by default, returns all)
     * @return  array   An array contains all IP and info. and Jaws_Error on error
     */
    function GetBlockedIPs($limit = 0, $offset = null)
    {
        $table = Jaws_ORM::getInstance()->table('policy_ipblock');
        $table->select('id', 'from_ip', 'to_ip', 'blocked:boolean');
        $table->limit($limit, $offset);
        $table->orderBy('id desc');
        return $table->fetchAll();
    }

    /**
     * Block a new IP range
     *
     * @access  public
     * @param   string  $ip the to be blocked IP address
     * @return  bool    True on success and Jaws_Error on errors
     */
    function AddIPRange($from_ip, $to_ip = null, $blocked = true)
    {
        $from_ip = ip2long($from_ip);
        if ($from_ip < 0) {
            $from_ip = $from_ip + 0xffffffff + 1;
        }

        if (empty($to_ip)) {
            $to_ip = $from_ip;
        } else {
            $to_ip = ip2long($to_ip);
            if ($to_ip < 0) $to_ip = $to_ip + 0xffffffff + 1;
        }

        $data = array();
        $data['from_ip'] = $from_ip;
        $data['to_ip'] = $to_ip;
        $data['blocked'] = (bool)$blocked;

        $table = Jaws_ORM::getInstance()->table('policy_ipblock');
        $res = $table->insert($data)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_IP_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLICY_RESPONSE_IP_NOT_ADDED', 'AddIPRange'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_IP_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Edit blocked IP range
     *
     * @access  public
     * @param   int     $id ID of the to-be-blocked IP range addresses
     * @param   string  $from_ip  The to-be-blocked from IP
     * @param   string  $to_ip    The to-be-blocked to IP
     * @return  bool    True on success and Jaws_Error on errors
     */
    function EditIPRange($id, $from_ip, $to_ip = null, $blocked = true)
    {
        $from_ip = ip2long($from_ip);
        if ($from_ip < 0) {
            $from_ip = $from_ip + 0xffffffff + 1;
        }

        if (empty($to_ip)) {
            $to_ip = $from_ip;
        } else {
            $to_ip = ip2long($to_ip);
            if ($to_ip < 0) $to_ip = $to_ip + 0xffffffff + 1;
        }

        $data = array();
        $data['from_ip'] = $from_ip;
        $data['to_ip'] = $to_ip;
        $data['blocked'] = (bool)$blocked;

        $table = Jaws_ORM::getInstance()->table('policy_ipblock');
        $res = $table->update($data)->where('id', (int)$id)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_IP_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('POLICY_RESPONSE_IP_NOT_DELETED', 'EditIPRange'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_IP_EDITED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Unblock an IP range
     *
     * @access  public
     * @param   int $id ID of the to be unblocked IP Band
     * @return  bool    True on successfull attempts and Jaws Error otherwise
     */
    function DeleteIPRange($id)
    {
        $table = Jaws_ORM::getInstance()->table('policy_ipblock');
        $res = $table->delete()->where('id', (int)$id)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_IP_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage());
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_IP_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Set IPBlocking block undefined ip
     *
     * @access  public
     * @param   bool    $blocked    blocked by default
     * @return  bool    True on success and Jaws error on failure
     */
    function IPBlockingBlockUndefined($blocked)
    {
        $res = $this->gadget->registry->update('block_undefined_ip',
            $blocked? 'true' : 'false');
        return $res;
    }

}