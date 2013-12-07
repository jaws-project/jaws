<?php
/**
 * Quotes Gadget
 *
 * @category   GadgetModel
 * @package    Quotes
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Quotes_Model_Quotes extends Jaws_Gadget_Model
{
    /**
     * Retrieves data of the quote
     *
     * @access  public
     * @param   int     $id     Quote ID
     * @return  mixed   Quote data array or Jaws_Error
     */
    function GetQuote($id)
    {
        $quotesTable = Jaws_ORM::getInstance()->table('quotes');
        $res = $quotesTable->select(
            'id:integer', 'gid:integer', 'title', 'quotation', 'quote_type:integer',
            'rank:integer', 'start_time', 'stop_time', 'show_title:boolean', 'published:boolean'
        );
        return $quotesTable->where('id', $id)->fetchRow();
    }

    /**
     * Retrieves quotes
     *
     * @param   int     $id
     * @param   int     $gid
     * @param   int     $limit
     * @param   int     $offset
     * @return  mixed   List of quotes or Jaws_Error
     */
    function GetQuotes($id = -1, $gid = -1, $limit = 0, $offset = null)
    {
        $quotesTable = Jaws_ORM::getInstance()->table('quotes');
        $quotesTable->select(
            'id:integer', 'gid:integer', 'title', 'quotation', 'quote_type:integer',
            'rank:integer', 'start_time', 'stop_time', 'show_title:boolean', 'published:boolean'
        );

        if (($id != -1) && ($gid != -1)) {
            $quotesTable->where('id', $id)->and()->where('gid', $gid);
        } elseif ($gid != -1) {
            $quotesTable->where('gid', $gid);
        } elseif ($id != -1) {
            $quotesTable->where('id', $id);
        }
        $res = $quotesTable->orderBy('id asc')->limit($limit, $offset)->fetchAll();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage());
        }

        return $res;
    }

    /**
     * Retrieves quotes that can be published
     *
     * @access  public
     * @param   int     $gid        Group ID
     * @param   int     $limit
     * @param   bool    $randomly
     * @return  array   List of quotes or Jaws_Error
     */
    function GetPublishedQuotes($gid, $limit = null, $randomly = false)
    {
        $now = $GLOBALS['db']->Date();

        $quotesTable = Jaws_ORM::getInstance()->table('quotes');
        $quotesTable->select('id:integer', 'title', 'quotation', 'rank:integer', 'show_title:boolean');
        $quotesTable->where('gid', $gid)->and()->where('published', true)->and();
        $quotesTable->openWhere()->where('start_time', '', 'is null')->or();
        $quotesTable->where('start_time', $now, '<=')->closeWhere()->and();
        $quotesTable->openWhere()->where('stop_time', '', 'is null')->or();
        $quotesTable->where('stop_time', $now, '>=')->closeWhere();

        if ($randomly) {
            $quotesTable->orderBy($quotesTable->random());
        } else {
            $quotesTable->orderBy('rank asc', 'id desc');
        }

        $res = $quotesTable->limit($limit)->fetchAll();
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return $res;
    }

    /**
     * Retrieves latest created quotes
     *
     * @access  public
     * @param   int     $limit
     * @param   bool    $randomly
     * @return  array   List of quotes or Jaws_Error
     */
    function GetRecentQuotes($limit = null, $randomly = false)
    {
        $now = $GLOBALS['db']->Date();

        $quotesTable = Jaws_ORM::getInstance()->table('quotes');
        $quotesTable->select('id:integer', 'title', 'quotation', 'rank:integer', 'show_title:boolean');
        $quotesTable->where('published', true)->and();
        $quotesTable->openWhere()->where('start_time', '', 'is null')->or();
        $quotesTable->where('start_time', $now, '<=')->closeWhere()->and();
        $quotesTable->openWhere()->where('stop_time', '', 'is null')->or();
        $quotesTable->where('stop_time', $now, '>=')->closeWhere();

        if ($randomly) {
            $quotesTable->orderBy($quotesTable->random());
        } else {
            $quotesTable->orderBy('id desc');
        }

        $res = $quotesTable->limit($limit)->fetchAll();
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return $res;
    }
}