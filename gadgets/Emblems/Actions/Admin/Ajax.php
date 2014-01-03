<?php
/**
 * Emblems AJAX API
 *
 * @category   Ajax
 * @package    Emblems
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Updates the emblem
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateEmblem()
    {
        @list($id, $data) = jaws()->request->fetch(array('0', '1:array'), 'post');
        $model = $this->gadget->model->loadAdmin('Emblems');
        $res = $model->UpdateEmblem($id, $data);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage());
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_UPDATED'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the emblem
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteEmblem()
    {
        @list($id) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->load('Emblems');
        $emblem = $model->GetEmblem($id);

        $model = $this->gadget->model->loadAdmin('Emblems');
        $res = $model->DeleteEmblem($id);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage());
        }

        // delete the file
        if (!empty($emblem['image'])) {
            Jaws_Utils::Delete(JAWS_DATA . 'emblems/' . $emblem['image']);
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_DELETED'), RESPONSE_NOTICE);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Fetches a limited array of emblems
     *
     * @access  public
     * @return  array   An array of emblems
     */
    function getData()
    {
        @list($limit) = jaws()->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Emblems');
        return $gadget->GetEmblems($limit);
    }
}
