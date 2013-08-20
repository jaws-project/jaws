<?php
/**
 * Emblems Admin Gadget
 *
 * @category   GadgetModelAdmin
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_Model_Admin_Emblems extends Jaws_Gadget_Model
{
    /**
     * Updates the emblem info in the database
     *
     * @access  public
     * @param   int     $id     ID That identifies the emblem
     * @param   string  $name   Name of the emblem
     * @param   string  $url    URL of the emblem
     * @param   string  $type   Type code of the emblem
     * @param   string  $status Status of the emblem
     * @return  mixed   True if query was successful and Jaws_Error on error
     */
    function UpdateEmblem($id, $title, $url, $type, $status)
    {
        $params['title']  = $title;
        $params['url']    = $url;
        $params['emblem_type']   = $type;
        $params['enabled'] = $status;
        $params['updated']    = $GLOBALS['db']->Date();

        $emblemTable = Jaws_ORM::getInstance()->table('emblem');
        $res = $emblemTable->update($params)->where('id', $id)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED', 'UpdateEmblem'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Adds a new emblem to the system and database
     *
     * @access  public
     * @param   string  $title      emblem title
     * @param   string  $url        URL of the emblem
     * @param   string  $file_url   Relative file url
     * @param   string  $type
     * @param   bool    $enabled
     * @return  mixed   True if successful, Jaws_Error otherwise
     */
    function AddEmblem($title, $url, $file_url, $type = 'P', $enabled = false)
    {
        $params['title']        = $title;
        $params['src']          = $file_url;
        $params['url']          = $url;
        $params['emblem_type']  = $type;
        $params['updated']      = $GLOBALS['db']->Date();
        $params['enabled']      = (bool) $enabled;

        $emblemTable = Jaws_ORM::getInstance()->table('emblem');
        $res = $emblemTable->insert($params)->exec();
        if (Jaws_Error::IsError($res)) {
            Jaws_Utils::delete(JAWS_DATA. 'emblems/'. $file_url);
            $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ERROR_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('EMBLEMS_ERROR_NOT_ADDED'), _t('EMBLEMS_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes an emblem
     *
     * @access  public
     * @param   int      $id     ID that identifies the emblem
     * @param   string   $src    Path to the emblem image
     * @return  mixed    True if success, Jaws_Error otherwise
     */
    function DeleteEmblem($id, $src)
    {
        $res = Jaws_ORM::getInstance()->table('emblem')->delete()->where('id', $id)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED', 'DeleteEmblem'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        if (!file_exists(JAWS_DATA . 'emblems/' . $src) || @unlink(JAWS_DATA . 'emblems/' . $src)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_DELETED'), RESPONSE_NOTICE);
            return true;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_FILE_NOT_DELETED'), RESPONSE_ERROR);
        return new Jaws_Error(_t('EMBLEMS_FILE_NOT_DELETED', 'DeleteEmblem'), _t('EMBLEMS_NAME'));
    }

}