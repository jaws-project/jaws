<?php
/**
 * Shoutbox AJAX API
 *
 * @category   Ajax
 * @package    Shoutbox
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Shoutbox_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->load('AdminModel');
    }


    /**
     * Update the properties
     *
     * @access  public
     * @param   int     $limit      Limit of shoutbox entries
     * @param   int     $max_strlen Maximum length of comment entry
     * @param   bool    $authority
     * @return  array   Response array (notice or error)
     */
    function UpdateProperties($limit, $max_strlen, $authority)
    {
        $this->gadget->CheckPermission('UpdateProperties');
        $this->_Model->UpdateProperties($limit, $max_strlen, $authority == 'true');
        return $GLOBALS['app']->Session->PopLastResponse();
    }
}