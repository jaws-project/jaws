<?php
/**
 * Class that deals with all the shared stuff in Jaws. For example
 * if a gadget wants to share a method or want to add a new host to
 * share some stuff, this class should be used.
 *
 * @category   Shared
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Shared
{
    /**
     * Adds a new shared method
     *
     * @access  public
     * @param   string   $gadget  Gadget name that has the shared method
     * @param   string   $method  Shared method
     * @return  bool     True(Success) or Jaws_Error(Failure)
     */
    function AddNewMethod($gadget, $method)
    {
        $params           = array();
        $params['gadget'] = $gadget;
        $params['method'] = $method;

        $sql = "
            SELECT
                COUNT([id])
            FROM [[shared_methods]]
            WHERE
                [gadget] = {gadget}
              AND
                [method] = {method}";

        $howmany = $GLOBALS['db']->queryOne($sql, $params);
        if (PEAR::isError($howmany) || $howmany > '0') {
            return new Jaws_Error(_t('SHARED_METHOD_NOT_ADDED'),
                                     __FUNCTION__);
        }

        $sql = "
            INSERT INTO [[shared_methods]]
                ([gadget], [method])
            VALUES
                ({gadget}, {method})";

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('SHARED_METHOD_NOT_ADDED'),
                                  __FUNCTION__);
        }

        return true;
    }

    /**
     * Deletes a shared method
     *
     * @access  public
     * @param   string   $gadget  Gadget name that has the shared method
     * @param   string   $method  Shared method
     * @return  bool     True(Success) or Jaws_Error(Failure)
     */
    function DeleteMethod($gadget, $method)
    {
        $result = $this->DeleteAllMethodAccess($gadget, $method);
        if (Jaws_Error::isError($result)) {
            return new Jaws_Error(_t('SHARED_METHOD_NOT_DELETED'),
                                     __FUNCTION__);
        }

        $params           = array();
        $params['gadget'] = $gadget;
        $params['method'] = $method;

        $sql = "
            DELETE FROM [[shared_methods]]
            WHERE
                [gadget] = {gadget}
              AND
                [method] = {method}";

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('SHARED_METHOD_NOT_DELETED'),
                                     __FUNCTION__);
        }

        return true;
    }

    /**
     * Gets a list of all shared method
     *
     * @access  public
     * @return  array    List of shared methods
     */
    function GetSharedMethods()
    {
        $sql = "
            SELECT
                [id], [gadget], [method]
            FROM [[shared_methods]]";

        $res = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED', 'GetSharedMethods'),
                                     __FUNCTION__);
        }

        return $res;
    }

    /**
     * Gets a list of all shared methods of a gadget
     *
     * @access  public
     * @param   string   $gadget   Gadget name
     * @return  array    List of shared methods
     */
    function GetSharedMethodsOfGadget($gadget)
    {
        $params = array();
        $params['gadget'] = $gadget;

        $sql = "
            SELECT
                [id], [gadget], [method]
            FROM [[shared_methods]]
            WHERE [gadget] = {gadget}";

        $res = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED', 'GetSharedMethodsOfGadget'),
                                  __FUNCTION__);
        }

        return $res;
    }

    /**
     * Gets information of a shared method by giving its shared method ID
     *
     * @access  public
     * @param   int      $id  id of method
     * @return  array    Properties of a shared method
     */
    function GetSharedMethodByID($id)
    {
        $params = array();
        $params['id'] = (int)$id;

        $sql = "
            SELECT
                 [id], [gadget], [method]
            FROM [[shared_methods]]
            WHERE [id] = {id}";

        $res = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED', 'GetSharedMethodByID'),
                                     __FUNCTION__);
        }

        return $res;
    }

    /**
     * Gets information of a shared method by giving its
     * gadget and shared method name
     *
     * @access  public
     * @param   string   $gadget  Gadget name that has the shared method
     * @param   string   $method  Shared method
     * @return  array    Properties of a shared method
     */
    function GetSharedMethod($gadget, $name)
    {
        $id = $this->GetSharedMethodID($gadget, $method);
        if (Jaws_Error::isError($id)) {
            return array();
        }

        return $this->GetSharedMethodByID($id);
    }

    /**
     * Adds a access rule to a shared method
     *
     * @access  public
     * @param   int      $shared   ID of shared method
     * @param   string   $source   Source to accept, drop or reject
     * @param   string   $type     Type of rule (ACCEPT, DROP or REJECT)
     * @return  bool     True(Success) or Jaws_Error(Failure)
     */
    function AddNewAccess($shared, $source = '*', $type = 'ACCEPT')
    {
        $allowedTypes = array('ACCEPT', 'DROP', 'REJECT');
        if (!in_array($type, $allowedTypes)) {
            return new Jaws_Error(_t('SHARED_METHOD_RULE_NOT_ADDED'),
                                     __FUNCTION__);
        }

        $params = array();
        $params['shared_method'] = $shared;
        $params['source']        = $source;
        $params['type']          = $type;
        $params['md5']           = '';

        //Source is a URI?
        if (preg_match("/^(http|https|ftp):\/\/([^\\s\"<>&]+)$/", $source)) {
            $params['md5']  = md5($source);
        }

        $sql = "
            INSERT INTO [[shared_methods_rules]]
                ([shared_method], [source], [rule_type], [md5value])
            VALUES
                ({shared_method}, {source}, {type}, {md5})";

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('SHARED_METHOD_RULE_NOT_ADDED'),
                                     __FUNCTION__);
        }

        return true;
    }

    /**
     * Updates information of an access rule
     *
     * @access  public
     * @param   int      $rule     Access Rule ID
     * @param   int      $shared   Shared method
     * @param   string   $source   Source to accept, drop or reject
     * @param   string   $type     Type of rule (ACCEPT, DROP or REJECT)
     * @return  bool     True(Success) or Jaws_Error(Failure)
     */
    function UpdateAccess($rule, $shared, $source = '*', $type = 'ACCEPT')
    {
        $allowedTypes = array('ACCEPT', 'DROP', 'REJECT');
        if (!in_array($type, $allowedTypes)) {
            return new Jaws_Error(_t('SHARED_METHOD_RULE_NOT_ADDED'),
                                     __FUNCTION__);
        }

        $params = array();
        $params['id']            = (int)$rule;
        $params['shared_method'] = (int)$shared;
        $params['source']        = $source;
        $params['type']          = $type;
        $params['md5']           = '';

        //Source is a URI?
        if (preg_match("/^(http|https|ftp):\/\/([^\\s\"<>&]+)$/", $source)) {
            $params['md5']  = md5($source);
        }

        $sql = "
        UPDATE [[shared_methods_rules]] SET
            [shared_method] = {shared_method},
            [source] = {source},
            [rule_type] = {type},
            [md5value] = {md5}
        WHERE [id] = {id}";

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('SHARED_METHOD_RULE_NOT_UPDATED'),
                                     __FUNCTION__);
        }

        return true;
    }

    /**
     * Deletes a shared method rule by its ID
     *
     * @access  public
     * @param   string   $id      Shared method rule
     * @return  bool     True(Success) or Jaws_Error(Failure)
     */
    function DeleteAccess($id)
    {
        $params = array();
        $params['id'] = (int)$id;

        $sql = "
            DELETE FROM [[shared_methods_rules]]
            WHERE [id] = {id}";


        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('SHARED_METHOD_RULE_NOT_DELETED'),
                                     __FUNCTION__);
        }

        return true;
    }

    /**
     * Deletes all shared method rules of by the given method and gadget name
     *
     * @access  public
     * @param   string   $gadget  Gadget name that has the shared method
     * @param   string   $method  Shared method
     * @return  bool     True(Success) or Jaws_Error(Failure)
     */
    function DeleteAllMethodAccess($gadget, $method)
    {
        //First, get the shared method ID
        $id = $this->GetSharedMethodID($gadget, $method);
        if ($id = (int)$id) {
            $params = array();
            $params['id'] = $id;

            $sql = "
                DELETE FROM [[shared_methods_rules]]
                WHERE [shared_method] = {id}";

            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error(_t('SHARED_METHOD_RULE_NOT_DELETED'),
                                      __FUNCTION__);
            }

            return true;
        }

        return new Jaws_Error(_t('SHARED_METHOD_RULE_NOT_DELETED'),
                                 __FUNCTION__);
    }

    /**
     * Get information of a access rule of a shared method, giving its ID
     *
     * @access  public
     * @return  array    Information of an access rule
     */
    function GetAccessRuleByID($id)
    {
        $params = array();
        $params['id'] = (int)$id;

        $sql = "
            SELECT
                [id], [shared_method], [source], [rule_type]
            FROM [[shared_methods_rules]]
            WHERE [id] = {id}";

        $res = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED', 'GetAccessRules'),
                                     __FUNCTION__);
        }

        return $res;
    }

    /**
     * Get all access rules
     *
     * @access  public
     * @return  array    List of access rules
     */
    function GetAccessRules()
    {
        $sql = "
            SELECT
                [id], [shared_method], [source], [rule_type]
            FROM [[shared_methods_rules]]";

        $res = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED', 'GetAccessRules'),
                                     __FUNCTION__);
        }

        return $res;
    }

    /**
     * Get all access rules that match certain access type
     *
     * @access  public
     * @param   string   $type   Access type
     * @return  array    List of access rules
     */
    function GetAccessRulesByType($type)
    {
        $params = array();
        $params['type'] = $type;

        $sql = "
            SELECT
                [id], [shared_method], [source], [rule_type]
            FROM [[shared_methods_rules]]
            WHERE [rule_type] = {type}";

        $res = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED', 'GetAccessRulesByID'),
                                     __FUNCTION__);
        }

        return $res;
    }

    /**
     * Get all access rules that match the 'ACCEPT' type
     *
     * @access  public
     * @return  array    List of access rules
     */
    function GetAllAcceptableRules()
    {
        return $this->GetAccessRulesByType('ACCEPT');
    }

    /**
     * Get all access rules that match the 'DROP' type
     *
     * @access  public
     * @return  array    List of access rules
     */
    function GetAllDropableRules()
    {
        return $this->GetAccessRulesByType('DROP');
    }

    /**
     * Get all access rules that match the 'REJECT' type
     *
     * @access  public
     * @return  array    List of access rules
     */
    function GetAllRejectableRules()
    {
        return $this->GetAccessRulesByType('REJECT');
    }

    /**
     * Get access rules of a gadget
     *
     * @access  public
     * @param   string   $gadget   Gadget name
     * @return  array    List of access rules
     */
    function GetAccessRulesOfGadget($gadget)
    {
        //First get allt he shared method of a gadget
        $methods = $this->GetSharedMethodsOfGadget($gadget);
        if (Jaws_Error::IsError($methods)) {
            return array();
        }

        $rulesOfGadget = array();
        foreach ($methods as $method) {
            $rules = $this->GetAccessRulesOfMethodByID($method['id']);
            if (!Jaws_Error::IsError($rules)) {
                $rulesOfGadget[] = $rules;
            }
        }

        if (count($rulesOfGadget) > 0) {
            $rulesOfGadget = $rulesOfGadget[0];
        }

        return $rulesOfGadget;
    }

    /**
     * Get access rules of a shared method, giving its ID
     *
     * @access  public
     * @param   int      $shared   ID of shared method
     * @return  array    List of access rules
     */
    function GetAccessRulesOfMethodByID($id)
    {
        $params = array();
        $params['id'] = (int)$id;

        $sql = "
            SELECT
                [[shared_methods_rules]].[id],
                [shared_method],
                [source],
                [rule_type],
                [[shared_methods]].[method] as method_name
            FROM [[shared_methods_rules]]
            INNER JOIN [[shared_methods]] ON [[shared_methods]].[id] = [[shared_methods_rules]].[shared_method]
            WHERE [shared_method] = {id}";

        $res = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED', 'GetAccessRulesByID'),
                                     __FUNCTION__);
        }

        return $res;
    }

    /**
     * Get access rules of a shared method, giving its gadget and shared method name
     *
     * @access  public
     * @param   string   $gadget   Gadget name that has the shared method
     * @param   string   $method   Shared method
     * @return  array    List of access rules
     */
    function GetAccessRulesOfMethod($gadget, $method)
    {
        $id = $this->GetSharedMethodID($gadget, $method);
        if (Jaws_Error::isError($id)) {
            return array();
        }

        return $this->GetAccesRulesOfMethodByID($id);
    }

    /**
     * Get true or false if user has access
     *
     * @access  public
     * @param   string   $gadget  Gadget name that has the shared method
     * @param   string   $method  Shared method
     * @param   string   $md5     MD5Key. It should be the URL on the other side but in MD5.
     * @param   string   $type    Type of access (by default uses the one in registry)
     * @return  bool     True if user can use the method
     */
    function HasAccess($gadget, $method, $md5, $type = '')
    {
        if (empty($md5) || strlen($md5) != 32) {
            $md5 = md5(uniqid());
        }

        //OK.. this is a bitch firewall, we should also classify by:
        // - netmasks
        // - wildcard
        // - network
        // - DNS
        // - Broadcast
        $params = array();
        $params['gadget']       = $gadget;
        $params['method']       = $method;
        $params['md5']          = $md5;
        $params['blogIP']       = $_SERVER['REMOTE_ADDR'];
        $params['blogHostname'] = gethostbyaddr($params['blogIP']);

        $sql = "
            SELECT [rule_type]
            FROM [[shared_methods]]
            INNER JOIN [[shared_methods_rules]] ON [[shared_methods_rules]].[shared_method] = [[shared_methods]].[id]
            WHERE
                [[shared_methods]].[gadget] = {gadget}
              AND
                [[shared_methods]].[method] = {method}
              AND
                ([[shared_methods_rules]].[md5value] = {md5}
              OR ";

        $dns = dns_get_record($params['blogDNS']);
        $sql.= "
                [[shared_methods_rules]].[source] = {blogHostname}
              OR
                [[shared_methods_rules]].[source] = {blogIP})";

        $res = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        if (count($res) > 0) {
            foreach ($res as $r) {
                switch($r['type']) {
                case 'ACCEPT':
                    return true;
                    break;
                case 'DROP':
                    return false;
                    break;
                case 'REJECT':
                    //REJECT is like DROP but returns an error with a message
                    return new Jaws_Error(_t('SHARED_METHOD_NOT_GRANTED'),
                                             __FUNCTION__);
                    break;
                default:
                    return false;
                    break;
                }
            }
        } else {
            $res = $GLOBALS['app']->Registry->fetch('default_rule', 'Shared');
            if ($res == 'ACCEPT') {
                return true;
            } elseif ($res == 'DROP') {
                return false;
            }

            return new Jaws_Error(_t('SHARED_METHOD_NOT_GRANTED'),
                                     __FUNCTION__);
        }
    }

    /**
     * Get the shared method ID
     *
     * @access  private
     * @param   string   $gadget  Gadget name that has the shared method
     * @param   string   $method  Shared method
     * @return  int      Shared Method ID or Jaws_Error(Failure)
     */
    function GetSharedMethodID($gadget, $method)
    {
        $params = array();
        $params['gadget'] = $gadget;
        $params['method'] = $method;

        $sql = "
            SELECT
                [id]
            FROM [[shared_methods]]
            WHERE [gadget] = {gadget} AND
                  [method] = {method}";

        $id = $GLOBALS['db']->queryOne($sql, $params);
        if ($id = (int)$id) {
            return $id;
        }

        return new Jaws_Error(_t('SHARED_METHOD_NOT_EXISTS'),
                                 __FUNCTION__);
    }

}