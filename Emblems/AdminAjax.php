<?php
/**
 * Emblems AJAX API
 *
 * @category   Ajax
 * @package    Emblems
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class EmblemsAdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Delete an emblem
     *
     * @access  public
     * @param   int     $id     Emblem id
     * @return  array   Response array (notice or error)
     */
    function DeleteEmblem($id)
    {
        $this->CheckSession('Emblems', 'DeleteEmblem');
        $emblemInfo = $this->_Model->GetEmblem($id);
        $src = $emblemInfo['src'];
        $this->_Model->DeleteEmblem($id, $src);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates an emblem
     *
     * @access  public
     * @param   int     $id
     * @param   string  $title
     * @param   string  $url
     * @param   string  $type
     * @param   string  $status
     * @return  array   Response array (notice or error)
     */
    function UpdateEmblem($id, $title, $url, $type, $status)
    {
        $this->CheckSession('Emblems', 'EditEmblem');
        $this->_Model->UpdateEmblem($id, $title, $url, $type, $status);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update emblems properties
     * @access  public
     * @param   int     $rows       Rows to limit
     * @param   string  $allow_url  Disply URL in emblems?
     * @return  array   Response array (notice or error)
     */
    function UpdateProperties($rows, $allow_url)
    {
        $this->CheckSession('Emblems', 'UpdateProperties');
        $this->_Model->UpdateProperties($rows, $allow_url);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get a limited array of emblems
     *
     * @access  public
     * @param   int     $limit  Limit of emblems
     * @return  array   An array of emblems
     */
    function GetData($limit)
    {
        $gadget = $GLOBALS['app']->LoadGadget('Emblems', 'AdminHTML');
        return $gadget->GetEmblems($limit);
    }

}
