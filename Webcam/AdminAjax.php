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
     * Constructor
     *
     * @access  public
     */
    function WebcamAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Get information of a webcam
     *
     * @access  public
     * @param   int     $id    Webcam ID
     * @return  array   Webcam information
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
     * Add a webcam
     *
     * @access  public
     * @param   string  $title       Title of the webcam frame
     * @param   string  $url         Url of the webcam image
     * @param   int     $refresh     The refresh time to reload the webcam
     * @return  array   Response (notice or error)
     */
    function NewWebcam($title, $url, $refresh)
    {
        $this->CheckSession('Webcam', 'AddWebcam');
        $this->_Model->NewWebcam($title, $url, $refresh);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update webcam information
     *
     * @access  public
     * @param   int     $id          The id of the webcam
     * @param   string  $title       Title of the webcam frame
     * @param   string  $url         Url of the webcam image
     * @param   int     $refresh     Refresh rate
     * @return  array   Response (notice or error)
     */
    function UpdateWebcam($id, $title, $url, $refresh)
    {
        $this->CheckSession('Webcam', 'EditWebcam');
        $this->_Model->UpdateWebcam($id, $title, $url, $refresh);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a webcam
     *
     * @access  public
     * @param   int     $id  Webcam's ID
     * @return  array  Response (notice or error)
     */
    function DeleteWebcam($id)
    {
        $this->CheckSession('Webcam', 'DeleteWebcam');
        $this->_Model->DeleteWebcam($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update the properties
     *
     * @access  public
     * @param   int     $limit Random Limit
     * @return  array   Response
     */
    function UpdateProperties($limit)
    {
        $this->CheckSession('Webcam', 'UpdateProperties');
        $this->_Model->UpdateProperties($limit);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Callback to show short URL's
     *
     * @access  private
     * @param   string  $url   Long URL
     * @return  string  Short URL
     */
    function ShowShortURL($url)
    {
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $clean_url = $xss->filter($url);
        if (strlen($url) > 40) {
            return "<a title=\"{$clean_url}\" href=\"{$clean_url}\">" .
                $xss->filter(substr($url, 0, 40)) . "...</a>";
        }

        return "<a title=\"{$clean_url}\" href=\"{$clean_url}\">".$clean_url."</a>";
    }

    /**
     * Get Webcams
     *
     * @access  public
     * @return  array   Array of webcams
     */
    function GetData($limit)
    {
        $gadget = $GLOBALS['app']->LoadGadget('Webcam', 'AdminHTML');
        return $gadget->GetWebcams($limit);
    }

}
