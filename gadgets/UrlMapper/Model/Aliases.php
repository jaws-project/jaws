<?php
/**
 * UrlMapper Core Gadget
 *
 * @category   GadgetModel
 * @package    UrlMapper
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Model_Aliases extends Jaws_Gadget_Model
{
    /**
     * Returns all aliases stored in DB
     *
     * @access  public
     * @return  array   List of URL aliases
     */
    function GetAliases()
    {
        $aliasesTable = Jaws_ORM::getInstance()->table('url_aliases');
        $result = $aliasesTable->select(array('id:integer', 'alias_url'))->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        return $result;
    }

    /**
     * Returns basic information of certain alias
     *
     * @access   public
     * @param    int      $id      Alias ID
     * @return   array    Alias information
     */
    function GetAlias($id)
    {
        $aliasesTable = Jaws_ORM::getInstance()->table('url_aliases');
        $result = $aliasesTable->select(array('id:integer', 'alias_url', 'real_url'))->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return $result;
    }

    /**
     * Checks if hash already exists or not
     *
     * @access   public
     * @param    string $hash   Alias HASH value
     * @return   bool   Exists/Doesn't exists
     */
    function AliasExists($hash)
    {
        $aliasesTable = Jaws_ORM::getInstance()->table('url_aliases');
        $result = $aliasesTable->select('count([id]):integer')->where('alias_hash', $hash)->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return ($result == '0') ? false : true;
    }

    /**
     * Returns the real path of an alias(given path), if no alias is found
     * it returns false
     *
     * @access  public
     * @param   string  $alias  Alias
     * @return  mixed   Real path(URL) or false
     */
    function GetAliasPath($alias)
    {
        $aliasesTable = Jaws_ORM::getInstance()->table('url_aliases');
        $result = $aliasesTable->select('real_url')->where('alias_hash', md5($alias))->fetchOne();
        if (Jaws_Error::IsError($result) || empty($result)) {
            return false;
        }

        return $result;
    }
}