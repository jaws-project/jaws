<?php
/**
 * Registry AJAX API
 *
 * @category   Ajax
 * @package    Registry
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */

class RegistryAdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Returns the registry keys
     *
     * @access  public
     * @return  array   Array with all registry keys
     */
    function GetAllRegistry()
    {
        $GLOBALS['app']->Registry->LoadAllFiles();
        $simpleArray = $GLOBALS['app']->Registry->GetSimpleArray();
        ksort($simpleArray);

        return $simpleArray;
    }

    /**
     * Returns the value of a registry key
     *
     * @access  public
     * @param   string  $key  Key name
     * @return  string  Value of key
     */
    function GetRegistryKey($key)
    {
        if (preg_match("#^/(gadgets|plugins\/parse_text)/(.*?)/(.*?)#i", $key, $matches)) {
            $GLOBALS['app']->Registry->LoadFile($matches[2]);
        }
        return $GLOBALS['app']->Registry->Get($key);
    }

    /**
     * Saves the value of a key
     *
     * @access  public
     * @param   string  $key   Key name
     * @param   string  $value Key value
     * @return  array   Response
     */
    function SetRegistryKey($key, $value)
    {
        if (preg_match("#^/(gadgets|plugins\/parse_text)/(.*?)/(.*?)#i", $key, $matches)) {
            $GLOBALS['app']->Registry->LoadFile($matches[2]);
            $GLOBALS['app']->Registry->Set($key, $value);
            $GLOBALS['app']->Registry->Commit($matches[2]);
        } else {
            $GLOBALS['app']->Registry->Set($key, $value);
            $GLOBALS['app']->Registry->Commit('core');
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('REGISTRY_KEY_SAVED'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Returns the acl keys
     *
     * @access  public
     * @return  array   Array with all acl keys
     */
    function GetAllACL()
    {
        $GLOBALS['app']->ACL->LoadAllFiles();
        $simpleArray = $GLOBALS['app']->ACL->GetSimpleArray();
        ksort($simpleArray);

        return $simpleArray;
    }

    /**
     * Returns the value of an ACL key
     *
     * @access  public
     * @param   string  $key  Key name
     * @return  string  Value of key
     */
    function GetACLKey($key)
    {
        if (preg_match("#^/ACL/gadgets/(.*?)/(.*?)#i", $key, $matches)) {
            $GLOBALS['app']->ACL->LoadFile($matches[1]);
        }

        return $GLOBALS['app']->ACL->Get($key);
    }

    /**
     * Saves the value of an ACL key
     *
     * @access  public
     * @param   string  $key   Key name
     * @param   string  $value Key value
     * @return  array   Response
     */
    function SetACLKey($key, $value)
    {
        if (preg_match("#^/ACL/gadgets/(.*?)/(.*?)#i", $key, $matches)) {
            $GLOBALS['app']->ACL->LoadFile($matches[1]);
            $GLOBALS['app']->ACL->Set($key, $value);
        } else {
            $GLOBALS['app']->ACL->Set($key, $value);
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('REGISTRY_KEY_SAVED'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}