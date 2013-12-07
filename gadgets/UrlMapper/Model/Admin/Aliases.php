<?php
/**
 * UrlMapper Core Gadget
 *
 * @category   GadgetModel
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Model_Admin_Aliases extends UrlMapper_Model_Aliases
{
    /**
     * Adds a new alias
     *
     * @access  public
     * @param   string  $alias  Alias value
     * @param   string  $url    Real URL
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function AddAlias($alias, $url)
    {
        if (trim($alias) == '' || trim($url) == '') {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_NOT_ADDED'), $this->gadget->name);
        }

        $data['real_url']    = $url;
        $data['alias_url']   = $alias;
        $data['alias_hash']  = md5($alias);

        if ($this->AliasExists($data['alias_hash'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_ALREADY_EXISTS'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_ALREADY_EXISTS'), $this->gadget->name);
        }


        $aliasesTable = Jaws_ORM::getInstance()->table('url_aliases');
        $result = $aliasesTable->insert($data)->exec();

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_NOT_ADDED'), $this->gadget->name);
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ALIAS_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates the alias
     *
     * @access  public
     * @param   int     $id     Alias ID
     * @param   string  $alias  Alias value
     * @param   string  $url    Real URL
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function UpdateAlias($id, $alias, $url)
    {
        if (trim($alias) == '' || trim($url) == '') {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_NOT_UPDATED'), $this->gadget->name);
        }

        if ($url{0} == '?') {
            $url = substr($url, 1);
        }

        $data['real_url']   = $url;
        $data['alias_url']  = $alias;
        $data['alias_hash'] = md5($alias);

        $aliasesTable = Jaws_ORM::getInstance()->table('url_aliases');
        $result = $aliasesTable->select('alias_hash')->where('id', $id)->fetchOne();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_NOT_UPDATED'), $this->gadget->name);
        }

        if ($result != $data['alias_hash']) {
            if ($this->AliasExists($data['alias_hash'])) {
                $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_ALREADY_EXISTS'), RESPONSE_ERROR);
                return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_ALREADY_EXISTS'), $this->gadget->name);
            }
        }

        $aliasesTable = Jaws_ORM::getInstance()->table('url_aliases');
        $result = $aliasesTable->update($data)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_NOT_UPDATED'), $this->gadget->name);
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ALIAS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the alias
     *
     * @access  public
     * @param   int     $id  Alias ID
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function DeleteAlias($id)
    {
        $aliasesTable = Jaws_ORM::getInstance()->table('url_aliases');
        $result = $aliasesTable->delete()->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_NOT_DELETED'), $this->gadget->name);
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ALIAS_DELETED'), RESPONSE_NOTICE);
        return true;
    }
}