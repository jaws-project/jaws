<?php
/**
 * Webcam AJAX API
 *
 * @category   Ajax
 * @package    Webcam
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class WebcamAdminAjax extends Jaws_Ajax
{
    /**
     * Gets information of a webcam
     *
     * @access  public
     * @param   int     $id  Webcam ID
     * @return  mixed   Array of webcam information or false on failure
     */
    function GetWebcam($id)
    {
        $webcamInfo = $this->_Model->GetWebcam($id);
        if (Jaws_Error::IsError($webcamInfo)) {
            return false; //we need to handle errors on ajax
        }

        return $webcamInfo;
    }

    /**
     * Adds a new webcam
     *
     * @access  public
     * @param   string  $title      Title of the webcam frame
     * @param   string  $url        Url of the webcam image
     * @param   int     $refresh    The refresh time to reload the webcam
     * @return  array   Response array (notice or error)
     */
    function NewWebcam($title, $url, $refresh)
    {
        $this->CheckSession('Webcam', 'AddWebcam');
        $this->_Model->NewWebcam($title, $url, $refresh);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates webcam information
     *
     * @access  public
     * @param   int     $id         Webcam ID
     * @param   string  $title      Title of the webcam frame
     * @param   string  $url        Url of the webcam image
     * @param   int     $refresh    Refresh rate
     * @return  array   Response array (notice or error)
     */
    function UpdateWebcam($id, $title, $url, $refresh)
    {
        $this->CheckSession('Webcam', 'EditWebcam');
        $this->_Model->UpdateWebcam($id, $title, $url, $refresh);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the webcam
     *
     * @access  public
     * @param   int     $id  Webcam ID
     * @return  array   Response array (notice or error)
     */
    function DeleteWebcam($id)
    {
        $this->CheckSession('Webcam', 'DeleteWebcam');
        $this->_Model->DeleteWebcam($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates properties
     *
     * @access  public
     * @param   int     $limit  The limitation
     * @return  array   Response array (notice or error)
     */
    function UpdateProperties($limit)
    {
        $this->CheckSession('Webcam', 'UpdateProperties');
        $this->_Model->UpdateProperties($limit);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Callback to display short URLs
     *
     * @access  private
     * @param   string  $url    Original URL
     * @return  string  Short URL
     */
    function ShowShortURL($url)
    {
        if (strlen($url) > 40) {
            return "<a title=\"{$url}\" href=\"{$url}\">" . substr($url, 0, 40) . "...</a>";
        }

        return "<a title=\"{$url}\" href=\"{$url}\">".$url."</a>";
    }

    /**
     * Gets webcams
     *
     * @access  public
     * @param   int     $limit  The limitation
     * @return  array   List of webcams
     */
    function GetData($limit)
    {
        $gadget = $GLOBALS['app']->LoadGadget('Webcam', 'AdminHTML');
        return $gadget->GetWebcams($limit);
    }

}
