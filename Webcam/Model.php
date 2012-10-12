<?php
/**
 * Webcam Gadget
 *
 * @category   GadgetModel
 * @package    Webcam
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class WebcamModel extends Jaws_Model
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
        $sql = '
            SELECT
                [id], [title], [url], [refresh]
            FROM [[webcam]]
            WHERE [id] = {id}';

        $row = $GLOBALS['db']->queryRow($sql, array('id' => $id));
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage(), 'SQL');
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
        $GLOBALS['db']->dbc->loadModule('Function', null, true);
        $rand = $GLOBALS['db']->dbc->function->random();
        $sql = '
            SELECT
                [id], [title], [url], [refresh]
            FROM [[webcam]]
            ORDER BY ' . $rand;

        $limit = $GLOBALS['app']->Registry->Get('/gadgets/Webcam/limit_random');
        $result = $GLOBALS['db']->setLimit($limit);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        $row = $GLOBALS['db']->queryRow($sql);
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage(), 'SQL');
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
        if (is_numeric($limit)) {
            $rs = $GLOBALS['db']->setLimit(10, $limit);
            if (Jaws_Error::IsError($rs)) {
                return new Jaws_Error($rs->getMessage(), 'SQL');
            }
        }

        $sql = '
            SELECT
                [id], [title], [url], [refresh]
            FROM [[webcam]]
            ORDER BY [title]';

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }
}