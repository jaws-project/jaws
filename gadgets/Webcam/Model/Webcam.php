<?php
/**
 * Webcam Gadget
 *
 * @category   GadgetModel
 * @package    Webcam
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Webcam_Model_Webcam extends Jaws_Gadget_Model
{
    /**
     * Gets properties of the webcam
     *
     * @access  public
     * @param   int     $id     Webcam ID
     * @return  mixed   Array of webcam properties or Jaws_Error on failure
     */
    function GetWebCam($id)
    {
        $webcamTable = Jaws_ORM::getInstance()->table('webcam');
        $webcamTable->select('id:integer', 'title', 'url', 'refresh:integer');
        $row = $webcamTable->where('id', $id)->fetchRow();

        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage());
        }

        if (isset($row['id'])) {
            return $row;
        }

        return new Jaws_Error(_t('WEBCAM_ERROR_WEBCAM_DOES_NOT_EXISTS'));
    }

    /**
     * Gets properties of a random webcam
     *
     * @access  public
     * @return  mixed   Array of webcam properties or Jaws_Error on failure
     */
    function GetRandomWebCam()
    {
        $webcamTable = Jaws_ORM::getInstance()->table('webcam');
        $webcamTable->select('id:integer', 'title', 'url', 'refresh:integer');
        $limit = $this->gadget->registry->fetch('limit_random');
        $row = $webcamTable->limit($limit)->orderBy($webcamTable->random())->fetchRow();
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage());
        }

        if (isset($row['id'])) {
            return $row;
        }

        return new Jaws_Error(_t('WEBCAM_ERROR_WEBCAM_NOWEBCAMS'));
    }

    /**
     * Gets list of available webcams
     *
     * @access  public
     * @param   mixed   $limit  Limit of data to retrieve (false = return all)
     * @return  mixed   Array of webcams or Jaws_Error on failure
     */
    function GetWebCams($limit = false)
    {
        $webcamTable = Jaws_ORM::getInstance()->table('webcam');
        $webcamTable->select('id:integer', 'title', 'url', 'refresh:integer')->orderBy('title');
        $result = $webcamTable->limit($limit)->fetchAll();

        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }
}