<?php
/**
 * UrlMapper Core Gadget
 *
 * @category   GadgetModel
 * @package    UrlMapper
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Model_ErrorMaps extends Jaws_Gadget_Model
{
    /**
     * Checks if hash already exists or not
     *
     * @access   public
     * @param    string  $url_hash   URL HASH value
     * @return   bool   Exists/Doesn't exists
     */
    function ErrorMapExists($url_hash)
    {
        $urlerrorsTable = Jaws_ORM::getInstance()->table('url_errors');
        $result = $urlerrorsTable->select('count([id]):integer')->where('url_hash', $url_hash)->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return ($result == '0') ? false : true;
    }
}