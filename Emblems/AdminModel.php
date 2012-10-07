<?php
/**
 * Emblems Admin Gadget
 *
 * @category   GadgetModelAdmin
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/Emblems/Model.php';

class EmblemsAdminModel extends EmblemsModel
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  boolean True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'emblems' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('EMBLEMS_NAME'));
        }

        // Create table structure
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // If you are here, then copy the default jaws and feeds images
        $emblems = array('jaws', 'php', 'apache', 'mysql', 'pgsql', 'xhtml', 'css', 'atom', 'rss');
        foreach ($emblems as $emblem) {
            copy(JAWS_PATH. "gadgets/Emblems/images/$emblem.png", $new_dir. "$emblem.png");
            Jaws_Utils::chmod($new_dir. "$emblem.png");
        }

        $variables = array();
        $variables['timestamp'] = $GLOBALS['db']->Date();

        // Dump database data
        $result = $this->installSchema('insert.xml', $variables, 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Put the config registry keys
        $GLOBALS['app']->Registry->NewKey('/gadgets/Emblems/rows', '1');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Emblems/allow_url', 'true');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  boolean  True on success and Jaws_Error on error
     */
    function UninstallGadget()
    {
        $result = $GLOBALS['db']->dropTable('emblem');
        if (Jaws_Error::IsError($result)) {
            $gName  = _t('EMBLEMS_NAME');
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
            $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
            return new Jaws_Error($errMsg, $gName);
        }

        //registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Emblems/rows');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Emblems/allow_url');

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  boolean  Success/Failure (Jaws_Error)
     */
    function UpdateGadget($old, $new)
    {
        if (version_compare($old, '0.7.0', '<')) {
            $result = $this->installSchema('schema.xml', '', "$old.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '0.8.0', '<')) {
            $base_path = $GLOBALS['app']->getDataURL() . 'emblems/';
            $sql = '
                SELECT [id], [src]
                FROM [[emblem]]';
            $emblems = $GLOBALS['db']->queryAll($sql);
            if (!Jaws_Error::IsError($emblems)) {
                foreach ($emblems as $emblem) {
                    if (!empty($emblem['src'])) {
                        if (strpos($emblem['src'], $base_path) !== 0) {
                            continue;
                        }
                        $emblem['src'] = substr($emblem['src'], strlen($base_path));
                        $sql = '
                            UPDATE [[emblem]] SET
                                [src] = {src}
                            WHERE [id] = {id}';
                        $res = $GLOBALS['db']->query($sql, $emblem);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Updates the emblem info in the database
     *
     * @access  public
     * @param   $id         integer     ID That identifies the emblem
     * @param   $name       string      Name of the emblem
     * @param   $url        string      URL of the emblem
     * @param   $type       string      Type code of the emblem
     * @param   $status     string      Status of the emblem
     * @return  boolean True if query was successful and Jaws_Error on error
     */
    function UpdateEmblem($id, $title, $url, $type, $status)
    {
        $sql = '
            UPDATE [[emblem]] SET
                [title] = {title},
                [url] = {url},
                [emblem_type] = {type},
                [enabled] = {status},
                [updated] = {now}
            WHERE [id] = {id}';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');

        $params = array();
        $params['title']  = $xss->parse($title);
        $params['url']    = $xss->parse($url);
        $params['type']   = $type;
        $params['status'] = $status;
        $params['now']    = $GLOBALS['db']->Date();
        $params['id']     = $id;

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED', 'UpdateEmblem'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates the gadget properties in the registry
     *
     * @access public
     * @param  $rows        integer Number of rows that will display the gadget
     * @param  $allow_url   boolean If the emblems will display the link or not
     * @return boolean      True if properties got updated, Jaws_Error otherwise
     */
    function UpdateProperties($rows, $allow_url)
    {
        $result = $GLOBALS['app']->Registry->Set('/gadgets/Emblems/rows', $rows);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('EMBLEMS_ERROR_PROPERTIES_NOT_UPDATED'), _t('EMBLEMS_NAME'));
        }
        $result = $GLOBALS['app']->Registry->Set('/gadgets/Emblems/allow_url', $allow_url);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('EMBLEMS_ERROR_PROPERTIES_NOT_UPDATED'),
                                 _t('EMBLEMS_NAME'));
        }

        $GLOBALS['app']->Registry->Commit('Emblems');
        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Adds a new emblem to the system and database
     *
     * @access  public
     * @param   $name       string  Name of the emblem
     * @param   $url        string  URL of the emblem
     * @param   $file_url   string  relative file url
     * @return  boolean True if successful, Jaws_Error otherwise
     */
    function AddEmblem($title, $url, $file_url, $type = 'P', $enabled = false)
    {
        $sql = '
            INSERT INTO [[emblem]]
                ([title], [src], [url], [emblem_type], [enabled], [updated])
            VALUES
                ({title}, {src}, {url}, {type}, {enabled}, {now})';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');

        $params = array();
        $params['title']   = $xss->parse($title);
        $params['src']     = $file_url;
        $params['url']     = $url;
        $params['now']     = $GLOBALS['db']->Date();
        $params['enabled'] = (bool) $enabled;
        $params['type']    = $type;

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            unlink($uploadfile);
            $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ERROR_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('EMBLEMS_ERROR_NOT_ADDED'), _t('EMBLEMS_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes an emblem
     *
     * @access public
     * @param  $id      integer ID that identifies the emblem
     * @param  $src     string  Path to the emblem image
     * @return boolean  True if success, Jaws_Error otherwise
     */
    function DeleteEmblem($id, $src)
    {
        $sql = 'DELETE FROM [[emblem]] WHERE [id] = {id}';
        $params = array();
        $params['id'] = $id;
        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED', 'DeleteEmblem'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        if (!file_exists(JAWS_DATA . 'emblems/' . $src) || unlink(JAWS_DATA . 'emblems/' . $src)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_DELETED'), RESPONSE_NOTICE);
            return true;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_FILE_NOT_DELETED'), RESPONSE_ERROR);
        return new Jaws_Error(_t('EMBLEMS_FILE_NOT_DELETED', 'DeleteEmblem'), _t('EMBLEMS_NAME'));
    }
}