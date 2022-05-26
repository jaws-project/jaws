<?php
/**
 * Emblems AJAX API
 *
 * @category   Ajax
 * @package    Emblems
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2005-2021 Jaws Development Group
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
        @list($id, $data) = $this->gadget->request->fetch(array('0', '1:array'), 'post');
        $data['url'] = Jaws_XSS::defilter($data['url']);
        $model = $this->gadget->model->loadAdmin('Emblems');
        $res = $model->UpdateEmblem($id, $data);
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage());
        }

        $this->gadget->session->push($this::t('UPDATED'), RESPONSE_NOTICE);
        return $this->gadget->session->pop();
    }

    /**
     * Deletes the emblem
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteEmblem()
    {
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Emblems');
        $emblem = $model->GetEmblem($id);

        $model = $this->gadget->model->loadAdmin('Emblems');
        $res = $model->DeleteEmblem($id);
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push(Jaws::t('ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage());
        }

        // delete the file
        if (!empty($emblem['image'])) {
            Jaws_FileManagement_File::delete(ROOT_DATA_PATH . 'emblems/' . $emblem['image']);
        }

        $this->gadget->session->push($this::t('DELETED'), RESPONSE_NOTICE);
        return $this->gadget->session->pop();
    }

    /**
     * Fetches a limited array of emblems
     *
     * @access  public
     * @return  array   An array of emblems
     */
    function getData()
    {
        @list($limit) = $this->gadget->request->fetchAll('post');
        $gadget = $this->gadget->action->loadAdmin('Emblems');
        return $gadget->GetEmblems($limit);
    }
}
