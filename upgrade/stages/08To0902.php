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
            return Jaws_Error::raiseError(
                _t('UPGRADE_DB_RESPONSE_CONNECT_FAILED'),
                0,
                JAWS_ERROR_WARNING
            );
        }

        // upgrade core database schema
        $old_schema = JAWS_PATH . 'upgrade/Resources/schema/0.9.0.1.xml';
        $new_schema = JAWS_PATH . 'upgrade/Resources/schema/0.9.0.2.xml';
        if (!file_exists($old_schema)) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', '0.9.0.1.xml'),
                0,
                JAWS_ERROR_ERROR
            );
        }

        if (!file_exists($new_schema)) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', '0.9.0.2.xml'),
                0,
                JAWS_ERROR_ERROR
            );
        }

        _log(JAWS_LOG_DEBUG,"Upgrading core schema");
        $result = $GLOBALS['db']->installSchema($new_schema, '', $old_schema);
        if (Jaws_Error::isError($result)) {
            if ($result->getCode() !== MDB2_ERROR_ALREADY_EXISTS) {
                return Jaws_Error::raiseError($result->getMessage(), 0, JAWS_ERROR_ERROR);
            }
        }

        // Create application
        include_once JAWS_PATH . 'include/Jaws.php';
        $GLOBALS['app'] = jaws();
        $GLOBALS['app']->Registry->Init();

        // convert acl key name to new format
        $sql = '
            SELECT
                [key_name], [key_value]
            FROM [[old_acl]]
            ORDER BY [key_name]';
        $keys = $GLOBALS['db']->queryAll($sql, array(), null, MDB2_FETCHMODE_DEFAULT, true);
        if (Jaws_Error::isError($keys)) {
            return Jaws_Error::raiseError($keys->getMessage(), 0, JAWS_ERROR_ERROR);
        }

        $usrSQL = 'SELECT [id] FROM [[users]] WHERE [username] = {username}';
        $aclSQL = '
            INSERT INTO [[new_acl]]
                ([component], [key_name], [key_subkey], [key_value], [max_value], [user], [group])
            VALUES
                ({component}, {key_name}, {key_subkey}, {key_value}, {max_value}, {user}, {group})';

        $params = array();
        $usersArray = array();
        unset($keys['/last_update']);

        // Trying to add missed acl keys
        _log(JAWS_LOG_DEBUG,"trying to add missed acl keys");
        $exgadgets = array(
            'Jms' => 'Components',
            'Chatbox' => 'Shoutbox',
            'RssReader' => 'FeedReader',
            'SimpleSite' => 'Sitemap',
        );
        $installed_gadgets = $GLOBALS['app']->Registry->fetch('gadgets_installed_items');
        $installed_gadgets = str_replace(
            array(',Components', ',Shoutbox', ',FeedReader', ',Sitemap'),
            array(',Jms', ',Chatbox', ',RssReader', ',SimpleSite'),
            $installed_gadgets
        );

        $igadgets = array_filter(array_map('trim', explode(',', $installed_gadgets)));
        foreach ($igadgets as $ig) {
            if (!array_key_exists("/ACL/gadgets/$ig/default", $keys)) {
                $keys["/ACL/gadgets/$ig/default"] = 'true';
            }
            if (!array_key_exists("/ACL/gadgets/$ig/default_admin", $keys)) {
                $keys["/ACL/gadgets/$ig/default_admin"] = 'false';
            }
            if (!array_key_exists("/ACL/gadgets/$ig/default_registry", $keys)) {
                $keys["/ACL/gadgets/$ig/default_registry"] = 'false';
            }
        }

        _log(JAWS_LOG_DEBUG,"trying to add ACLs keys from old to new table");
        foreach ($keys as $key => $value) {
            $value = ($value == 'true');
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

            // exchange old to new name
            if (in_array($component, array_keys($exgadgets))) {
                $component = $exgadgets[$component];
            }

            // skip adding removed registry gadget
            if ($component == 'Registry') {
                continue;
            }

            $params['component']  = $component;
            $params['key_name']   = $new_key_name;
            $params['key_subkey'] = '';
            $params['key_value']  = (int)$value;
            $params['max_value']  = 0;
            $params['user']  = $user;
            $params['group'] = $group;
            $res = $GLOBALS['db']->query($aclSQL, $params);
            if (Jaws_Error::IsError($res)) {
                if (MDB2_ERROR_CONSTRAINT != $res->getCode()) {
                    return Jaws_Error::raiseError($res->getMessage(), 0, JAWS_ERROR_ERROR);
                }
            }
        }

        _log(JAWS_LOG_DEBUG,"Droping old acl table");
        $result = $GLOBALS['db']->dropTable('old_acl');
        if (Jaws_Error::isError($result)) {
            return $result;
        }

        // upgrade core database schema - next step
        $old_schema = JAWS_PATH . 'upgrade/Resources/schema/0.9.0.2.xml';
        $new_schema = JAWS_PATH . 'upgrade/Resources/schema/schema.xml';
        if (!file_exists($old_schema)) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', '0.9.0.2.xml'),
                0,
                JAWS_ERROR_ERROR
            );
        }

        if (!file_exists($new_schema)) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', 'schema.xml'),
                0,
                JAWS_ERROR_ERROR
            );
        }

        _log(JAWS_LOG_DEBUG,"Upgrading core schema");
        $result = $GLOBALS['db']->installSchema($new_schema, '', $old_schema);
        if (Jaws_Error::isError($result)) {
            if ($result->getCode() !== MDB2_ERROR_ALREADY_EXISTS) {
                return Jaws_Error::raiseError($result->getMessage(), 0, JAWS_ERROR_ERROR);
            }
        }

        return true;
    }

}