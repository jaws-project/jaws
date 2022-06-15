<?php
/**
 * Webcam Gadget Admin
 *
 * @category   GadgetModel
 * @package    Webcam
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Webcam_Model_Admin_Webcam extends Jaws_Gadget_Model
{
    /**
     * Inserts a new webcam
     *
     * @access  public
     * @param   string  $title      Title of the webcam frame
     * @param   string  $url        URL of the webcam image
     * @param   string  $refresh    The refresh time to reload the webcam
     * @return  mixed   True if query was successful, otherwise returns false.
     */
    function NewWebcam($title, $url, $refresh)
    {
        $data['title']   = $title;
        $data['url']     = $url;
        $data['refresh'] = $refresh;

        $webcamTable = Jaws_ORM::getInstance()->table('webcam');
        $result = $webcamTable->insert($data)->exec();

        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push($this::t('ERROR_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_NOT_ADDED'));
        }

        $this->gadget->session->push($this::t('ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates webcam info
     *
     * @access  public
     * @param   int     $id          Webcam ID
     * @param   string  $title       Title of the webcam frame
     * @param   string  $url         URL of the webcam image
     * @param   string  $refresh     Refresh rate
     * @return  mixed   True if query was successful, otherwise returns false
     */
    function UpdateWebcam($id, $title, $url, $refresh)
    {
        $data['title']   = $title;
        $data['url']     = $url;
        $data['refresh'] = $refresh;

        $webcamTable = Jaws_ORM::getInstance()->table('webcam');
        $result = $webcamTable->update($data)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push($this::t('ERROR_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_NOT_UPDATED'));
        }

        $this->gadget->session->push($this::t('UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the webcam
     *
     * @access  public
     * @param   int     $id Webcam ID
     * @return  mixed   True if query was successful, otherwise returns false
     */
    function DeleteWebcam($id)
    {
        $webcamTable = Jaws_ORM::getInstance()->table('webcam');
        $result = $webcamTable->delete()->where('id', $id)->exec();

        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push($this::t('ERROR_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_NOT_UPDATED'));
        }

        $this->gadget->session->push($this::t('DELETED'), RESPONSE_NOTICE);
        return true;
    }
}