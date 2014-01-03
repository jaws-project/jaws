<?php
/**
 * UrlMapper Core Gadget
 *
 * @category   GadgetModel
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2006-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Model_Admin_ErrorMaps extends UrlMapper_Model_ErrorMaps
{
    /**
     * Returns the error map
     *
     * @access  public
     * @param   int     $id Error Map ID
     * @return  mixed   Array of Error Map otherwise Jaws_Error
     */
    function GetErrorMap($id)
    {
        $params = array();
        $params['id'] = $id;

        $errorsTable = Jaws_ORM::getInstance()->table('url_errors');
        $result = $errorsTable->select('url', 'code', 'new_url', 'new_code')->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $result;
    }

    /**
     * Adds a new error map
     *
     * @access  public
     * @param   string  $url        source url
     * @param   int     $code       code
     * @param   string  $new_url    destination url
     * @param   int     $new_code   new code
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function AddErrorMap($url, $code, $new_url = '', $new_code = 0)
    {
        $data['url'] = $url;
        $data['url_hash'] = md5($url);
        $data['code'] = $code;
        $data['new_url'] = $new_url;
        $data['new_code'] = $new_code;
        $data['hits'] = 1;
        $data['createtime'] = $GLOBALS['db']->Date();
        $data['updatetime'] = $GLOBALS['db']->Date();

        $errorsTable = Jaws_ORM::getInstance()->table('url_errors');
        $result = $errorsTable->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Update the error map
     *
     * @access  public
     * @param   int     $id         error map id
     * @param   string  $url        source url
     * @param   string  $code       code
     * @param   string  $new_url    destination url
     * @param   string  $new_code   new code
     * @return  array   Response array (notice or error)
     */
    function UpdateErrorMap($id, $url, $code, $new_url, $new_code)
    {
        $errorsTable = Jaws_ORM::getInstance()->table('url_errors');
        $result = $errorsTable->select('url_hash')->where('id', $id)->fetchOne();


        $data['url'] = $url;
        $data['url_hash'] = md5($url);
        $data['code'] = $code;
        $data['new_url'] = $new_url;
        $data['new_code'] = $new_code;
        $data['updatetime'] = $GLOBALS['db']->Date();

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ERRORMAP_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ERRORMAP_NOT_UPDATED'));
        }

        if ($result != $data['url_hash']) {
            if ($this->ErrorMapExists($data['url_hash'])) {
                $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ERRORMAP_ALREADY_EXISTS'), RESPONSE_ERROR);
                return new Jaws_Error(_t('URLMAPPER_ERROR_ERRORMAP_ALREADY_EXISTS'));
            }
        }

        $errorsTable = Jaws_ORM::getInstance()->table('url_errors');
        $result = $errorsTable->update($data)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ERRORMAP_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ERRORMAP_NOT_UPDATED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERRORMAP_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the error maps
     *
     * @access  public
     * @param   array   $ids     Error map IDs
     * @return  array   Response array (notice or error)
     */
    function DeleteErrorMaps($ids)
    {
        $errorsTable = Jaws_ORM::getInstance()->table('url_errors');
        $result = $errorsTable->delete()->where('id', $ids, 'in')->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ERRORMAP_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ERRORMAP_NOT_DELETED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERRORMAP_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Get list of error maps
     *
     * @access  public
     * @param   int     $limit
     * @param   int     $offset
     * @return  array   Grid data
     */
    function GetErrorMaps($limit, $offset)
    {
        $errorsTable = Jaws_ORM::getInstance()->table('url_errors');
        $errorsTable->select(
            'id:integer', 'url', 'code:integer', 'new_url', 'new_code', 'hits:integer',
            'createtime', 'updatetime');
        $result = $errorsTable->limit($limit, $offset)->orderBy('createtime')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        return $result;
    }

    /**
     * Gets records count for error maps datagrid
     *
     * @access  public
     * @return  int   ErrorMaps row counts
     */
    function GetErrorMapsCount()
    {
        $errorsTable = Jaws_ORM::getInstance()->table('url_errors');
        $res = $errorsTable->select('count([id]):integer')->fetchOne();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage());
        }

        return $res;
    }

    /**
     * Get HTTP error of reguested URL
     *
     * @access  public
     * @param   string  $reqURL
     * @param   int     $code
     * @return  mixed   Error data array on success, Jaws_Error otherwise
     */
    function GetHTTPError($reqURL, $code)
    {
        $errorsTable = Jaws_ORM::getInstance()->table('url_errors');
        $errorsTable->select('id:integer', 'new_url as url', 'new_code as code:integer');
        $errorMap = $errorsTable->where('url_hash', md5($reqURL))->fetchRow();
        if (Jaws_Error::IsError($errorMap) || empty($errorMap)) {
            if (empty($errorMap)) {
                $this->AddErrorMap($reqURL, $code);
            }
        } else {
            $errorsTable = Jaws_ORM::getInstance()->table('url_errors');
            $result = $errorsTable->update(
                array(
                    'hits' => $errorsTable->expr('hits + ?', 1)
                )
            )->where('id', $errorMap['id'])->exec();

            if (Jaws_Error::IsError($result)) {
                // do nothing
            }
        }

        return $errorMap;
    }

}