<?php
/**
 * Webcam Gadget Admin
 *
 * @category   GadgetModel
 * @package    Webcam
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/Webcam/Model.php';

class WebcamAdminModel extends WebcamModel
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //Registry keys.
        $this->gadget->AddRegistry('limit_random', '3');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function UninstallGadget()
    {
        $result = $GLOBALS['db']->dropTable('webcam');
        if (Jaws_Error::IsError($result)) {
            $gName  = _t('WEBCAM_NAME');
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
            $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
            return new Jaws_Error($errMsg, $gName);
        }

        // registry keys
        $this->gadget->DelRegistry('limit_random');

        return true;
    }

    /**
     * Updates the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function UpdateGadget($old, $new)
    {
        $result = $this->installSchema('schema.xml', '', "$old.xml");
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys

        return true;
    }

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
        $params            = array();
        $params['title']   = $title;
        $params['url']     = $url;
        $params['refresh'] = $refresh;
        $sql = '
            INSERT INTO [[webcam]]
               ([title], [url], [refresh])
            VALUES
               ({title}, {url}, {refresh})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('WEBCAM_ERROR_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('WEBCAM_ERROR_NOT_ADDED'), _t('WEBCAM_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('WEBCAM_ADDED'), RESPONSE_NOTICE);
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
        $params            = array();
        $params['id']      = $id;
        $params['title']   = $title;
        $params['url']     = $url;
        $params['refresh'] = $refresh;
        $sql = '
            UPDATE [[webcam]] SET
                [title]   = {title},
                [url]     = {url},
                [refresh] = {refresh}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('WEBCAM_ERROR_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('WEBCAM_ERROR_NOT_UPDATED'), _t('WEBCAM_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('WEBCAM_UPDATED'), RESPONSE_NOTICE);
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
        $sql = 'DELETE FROM [[webcam]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, array('id' => $id));
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('WEBCAM_ERROR_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('WEBCAM_ERROR_NOT_UPDATED'), _t('WEBCAM_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('WEBCAM_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates properties of the gadget
     *
     * @access  public
     * @param   int     $limit  The limitation
     * @return  mixed   True if change is successful, if not, returns Jaws_Error on any error
     */
    function UpdateProperties($limit)
    {
        $res = $this->SetRegistry('limit_random', $limit);
        if ($res || !Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('WEBCAM_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
            return true;
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('WEBCAM_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
        return new Jaws_Error(_t('WEBCAM_ERROR_PROPERTIES_NOT_UPDATED'), _t('WEBCAM_NAME'));
    }

}