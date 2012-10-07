<?php
/**
 * RssReader Gadget
 *
 * @category   Gadget
 * @package    RssReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class RssReaderHTML extends Jaws_GadgetHTML
{
    /**
     * Default action to be run if none is defined.
     *
     * @access public
     * @return string HTML content of Default action
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('RssReader', 'LayoutHTML');
        return $layoutGadget->Display($GLOBALS['app']->Registry->Get('/gadgets/RssReader/default_feed'));
    }

    /**
     * Get the dcDate of an item
     *
     * From planet-php.net source code
     *
     * @access  private
     * @param   array    $item    Item to look for the date
     * @param   int      $offset  Offset of item(index)
     * @param   boolean  $returnNull Should it return false?
     * @return  string   The correct dcDate
     */
    function GetDCDate($item, $nowOffset = 0, $returnNull = false)
    {
        if (isset($item['dc']['date'])) {
            $dcdate = $this->FixDate($item['dc']['date']);
        } elseif (isset($item['pubdate'])) {
            $dcdate = $this->FixDate($item['pubdate']);
        } elseif (isset($item['issued'])) {
            $dcdate = $this->FixDate($item['issued']);
        } elseif (isset($item['created'])) {
            $dcdate = $this->FixDate($item['created']);
        } elseif (isset($item['modified'])) {
            $dcdate = $this->FixDate($item['modified']);
        } elseif ($returnNull) {
            return NULL;
        } else {
            //TODO: Find a better alternative here
            $dcdate = gmdate('Y-m-d H:i:s O', time() + $nowOffset);
        }
        return $dcdate;
    }

    /**
     * Fix the date format
     *
     * @access  private
     * @param   string  $date  Date to fix
     * @return  string  New date format
     */
    function FixDate($date)
    {
        $date =  preg_replace('/([0-9])T([0-9])/', '$1 $2', $date);
        $date =  preg_replace('/([\+\-][0-9]{2}):([0-9]{2})/', '$1$2', $date);
        $date =  gmdate('Y-m-d H:i:s O', strtotime($date));
        return $date;
    }

    /**
     * get requested feed
     * @access  public
     */
    function GetFeed()
    {
        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');

        $layoutGadget = $GLOBALS['app']->LoadGadget('RssReader', 'LayoutHTML');
        return $layoutGadget->Display($id);
    }
}