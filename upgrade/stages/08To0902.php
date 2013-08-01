<?php
/**
 * Jaws Upgrade Stage - From 0.8 to 0.9.0
 *
 * @category   Application
 * @package    UpgradeStage
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_08To0902 extends JawsUpgraderStage
{
    /**
     * Builds the upgader page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $tpl = new Jaws_Template(false);
        $tpl->Load('display.html', 'stages/08To0902/templates');
        $tpl->SetBlock('08To0902');

        $tpl->setVariable('lbl_info',  _t('UPGRADE_VER_INFO', '0.8', '0.9.0'));
        $tpl->setVariable('lbl_notes', _t('UPGRADE_VER_NOTES'));
        $tpl->SetVariable('next',      _t('GLOBAL_NEXT'));

        $tpl->ParseBlock('08To0902');
        return $tpl->Get();
    }

    /**
     * Does any actions required to finish the stage, such as DB queries.
     *
     * @access  public
     * @return  bool|Jaws_Error  Either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function Run()
    {
        // Connect to database
        require_once JAWS_PATH . 'include/Jaws/DB.php';
        $GLOBALS['db'] = new Jaws_DB($_SESSION['upgrade']['Database']);
        if (Jaws_Error::IsError($GLOBALS['db'])) {
            _log(JAWS_LOG_DEBUG,"There was a problem connecting to the database, please check the details and try again");
            return new Jaws_Error(_t('UPGRADE_DB_RESPONSE_CONNECT_FAILED'), 0, JAWS_ERROR_WARNING);
        }

        // upgrade core database schema
        $old_schema = JAWS_PATH . 'upgrade/schema/0.9.0.1.xml';
        $new_schema = JAWS_PATH . 'upgrade/schema/0.9.0.2.xml';
        if (!file_exists($old_schema)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', '0.9.0.1.xml'),0 , JAWS_ERROR_ERROR);
        }

        if (!file_exists($new_schema)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', '0.9.0.2.xml'),0 , JAWS_ERROR_ERROR);
        }

        _log(JAWS_LOG_DEBUG,"Upgrading core schema");
        $result = $GLOBALS['db']->installSchema($new_schema, '', $old_schema);
        if (Jaws_Error::isError($result)) {
            _log(JAWS_LOG_ERROR, $result->getMessage());
            if ($result->getCode() !== MDB2_ERROR_ALREADY_EXISTS) {
                return new Jaws_Error($result->getMessage(), 0, JAWS_ERROR_ERROR);
            }
        }

        // convert acl key name to new format
        $sql = 'SELECT [old_key_name], [old_key_value] FROM [[acl]]';
        $keys = $GLOBALS['db']->queryAll($sql, array(), null, MDB2_FETCHMODE_DEFAULT, true);
        if (Jaws_Error::isError($keys)) {
            _log(JAWS_LOG_WARNING, $keys->getMessage());
            return new Jaws_Error($keys->getMessage(), 0, JAWS_ERROR_ERROR);
        }

        unset($keys['/last_update']);
        $aclSQL = '
            UPDATE [[acl]] SET
                [component] = {component},
                [new_key_name] = {new_key_name},
                [key_subkey] = {key_subkey},
                [key_value2] = {new_key_value},
                [user] = {user},
                [group] = {group}
            WHERE [old_key_name] = {old_key_name}';
        $usrSQL = 'SELECT [id] FROM [[users]] WHERE [username] = {username}';

        $params = array();
        $usersArray = array();
        foreach ($keys as $key => $value) {
            $value = ($value == 'true');
            $params['old_key_name'] = $key;
            $key = substr($key, 5);
            $key = explode('/', $key);
            switch ($key[0]) {
                case 'users':
                    $group = 0;
                    $component = $key[3];
                    $new_key_name = $key[4];
                    if (!isset($usersArray[$key[1]])) {
                        $usersArray[$key[1]] = $GLOBALS['db']->queryOne($usrSQL, array('username' => $key[1]));
                    }

                    if (Jaws_Error::IsError($usersArray[$key[1]]) || empty($usersArray[$key[1]])) {
                        continue;
                    }
                    $user = (int)$usersArray[$key[1]];

                    break;

                case 'groups':
                    $user = 0;
                    $group = (int)$key[1];
                    $component = $key[3];
                    $new_key_name = $key[4];
                    break;

                default:
                    $user = 0;
                    $group = 0;
                    $component = $key[1];
                    $new_key_name = $key[2];
            }

            $params['component']     = $component;
            $params['new_key_name']  = $new_key_name;
            $params['key_subkey']    = '';
            $params['new_key_value'] = (int)$value;
            $params['user']  = $user;
            $params['group'] = $group;
            $res = $GLOBALS['db']->query($aclSQL, $params);
            if (Jaws_Error::IsError($res)) {
                _log(JAWS_LOG_WARNING, $res->getMessage());
                return new Jaws_Error($res->getMessage(), 0, JAWS_ERROR_ERROR);
            }

        }

        // delete outdated registry keys
        $sql = '
            DELETE
            FROM [[acl]]
            WHERE
                [old_key_name] = {key1}';
        $params = array();
        $params['key1'] = '/last_update';

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            _log(JAWS_LOG_ERROR, $res->getMessage());
            return $res;
        }

        // upgrade core database schema - next step
        $old_schema = JAWS_PATH . 'upgrade/schema/0.9.0.2.xml';
        $new_schema = JAWS_PATH . 'upgrade/schema/schema.xml';
        if (!file_exists($old_schema)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', '0.9.0.2.xml'),0 , JAWS_ERROR_ERROR);
        }

        if (!file_exists($new_schema)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', 'schema.xml'),0 , JAWS_ERROR_ERROR);
        }

        _log(JAWS_LOG_DEBUG,"Upgrading core schema");
        $result = $GLOBALS['db']->installSchema($new_schema, '', $old_schema);
        if (Jaws_Error::isError($result)) {
            _log(JAWS_LOG_ERROR, $result->getMessage());
            if ($result->getCode() !== MDB2_ERROR_ALREADY_EXISTS) {
                return new Jaws_Error($result->getMessage(), 0, JAWS_ERROR_ERROR);
            }
        }

        return true;
    }

}