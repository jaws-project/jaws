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
class Upgrader_08To0901 extends JawsUpgraderStage
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
        $tpl->Load('display.html', 'stages/08To0901/templates');
        $tpl->SetBlock('08To0901');

        $tpl->setVariable('lbl_info',  _t('UPGRADE_VER_INFO', '0.8', '0.9.0'));
        $tpl->setVariable('lbl_notes', _t('UPGRADE_VER_NOTES'));
        $tpl->SetVariable('next',      _t('GLOBAL_NEXT'));

        $tpl->ParseBlock('08To0901');
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
        $old_schema = JAWS_PATH . 'upgrade/Resources/schema/0.8.18.xml';
        $new_schema = JAWS_PATH . 'upgrade/Resources/schema/0.9.0.1.xml';
        if (!file_exists($old_schema)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', '0.8.18.xml'),0 , JAWS_ERROR_ERROR);
        }

        if (!file_exists($new_schema)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', '0.9.0.1.xml'),0 , JAWS_ERROR_ERROR);
        }

        _log(JAWS_LOG_DEBUG,"Upgrading core schema");
        $result = $GLOBALS['db']->installSchema($new_schema, '', $old_schema);
        if (Jaws_Error::isError($result)) {
            _log(JAWS_LOG_ERROR, $result->getMessage());
            if ($result->getCode() !== MDB2_ERROR_ALREADY_EXISTS) {
                return new Jaws_Error($result->getMessage(), 0, JAWS_ERROR_ERROR);
            }
        }

        // convert registry key name to new format
        $sql = 'SELECT [old_key_name], [key_value] FROM [[registry]]';
        $keys = $GLOBALS['db']->queryAll($sql, array(), null, MDB2_FETCHMODE_DEFAULT, true);
        if (Jaws_Error::isError($keys)) {
            _log(JAWS_LOG_ERROR, $keys->getMessage());
            return new Jaws_Error($keys->getMessage(), 0, JAWS_ERROR_ERROR);
        }

        $keys['/gadgets/enabled_items'] = $keys['/gadgets/core_items']. $keys['/gadgets/enabled_items']. ',';
        unset($keys['/gadgets/allowurl_items']);
        
        $sql = '
            UPDATE [[registry]] SET
                [component] = {component},
                [new_key_name] = {new_key_name},
                [key_value] = {new_key_value}
            WHERE [old_key_name] = {old_key_name}';
        $params = array();
        foreach ($keys as $key => $value) {
            $params['old_key_name'] = $key;
            $key = trim($key, '/');
            $key = explode('/', $key);
            switch ($key[0]) {
                case 'config':
                case 'network':
                    if ($key[1] == 'cookie') {
                        $component = 'Settings';
                        $new_key_name = 'cookie_'. $key[2];
                    } else {
                        if (in_array($key[1], array('anon_activation', 'anon_group',
                            'anon_register', 'anon_repetitive_email', 'auth_method'))
                        ) {
                            $component = 'Users';
                            $new_key_name = ($key[1] == 'auth_method')? 'authtype' : $key[1];
                        } else {
                            if (in_array($key[1], array('copyright', 'custom_meta', 'title_separator'))) {
                                $component = 'Settings';
                                $new_key_name = 'site_'. $key[1];
                            } else {
                                $component = 'Settings';
                                $new_key_name = $key[1];
                            }
                        }
                    }
                    break;

                case 'policy':
                    $component = 'Policy';
                    $new_key_name = $key[1];
                    break;

                case 'map':
                    $component = 'UrlMapper';
                    $new_key_name = 'map_'. $key[1];
                    break;

                case 'crypt':
                    $component = 'Policy';
                    $new_key_name = 'crypt_'. $key[1];
                    break;

                case 'gadgets':
                    switch ($key[1]) {
                        case 'enabled_items':
                            $component = '';
                            $new_key_name = 'gadgets_installed_items';
                            $value = str_replace(
                                array(',Jms', ',Chatbox', ',RssReader', ',SimpleSite', ',Registry'),
                                array(',Components', ',Shoutbox', ',FeedReader', ',Sitemap', ''),
                                $value
                            );
                            break;

                        case 'core_items':
                            $component = '';
                            $new_key_name = 'gadgets_disabled_items';
                            $value = ',';
                            break;

                        case 'autoload_items':
                            $component = '';
                            $new_key_name = 'gadgets_autoload_items';
                            $value.= ',';
                            break;

                        case 'Jms':
                            $component = 'Components';
                            $new_key_name = $key[2];
                            break;

                        case 'Chatbox':
                            $component = 'Shoutbox';
                            $new_key_name = $key[2];
                            break;

                        case 'RssReader':
                            $component = 'FeedReader';
                            $new_key_name = $key[2];
                            break;

                        case 'SimpleSite':
                            $component = 'Sitemap';
                            $new_key_name = $key[2];
                            break;

                        default:
                            $component = $key[1];
                            $new_key_name = $key[2];
                    }
                    break;

                case 'plugins':
                    switch ($key[1]) {
                        case 'parse_text':
                            if ($key[2] == 'enabled_items') {
                                $component = '';
                                $new_key_name = 'plugins_installed_items';
                                $value.= ',';
                                break;
                            }
                            $key[1] = $key[2];
                            $key[2] = $key[3];

                        default:
                            $component = $key[1];
                            $new_key_name = $key[2];
                    }
                    break;

                default:
                    $component = '';
                    $new_key_name = $key[0];
            }

            $params['component']     = $component;
            $params['new_key_name']  = $new_key_name;
            $params['new_key_value'] = $value;
            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                _log(JAWS_LOG_ERROR, $res->getMessage());
                return new Jaws_Error($res->getMessage(), 0, JAWS_ERROR_ERROR);
            }
        }

        // delete outdated registry keys
        $sql = '
            DELETE
            FROM [[registry]]
            WHERE
                [old_key_name] = {key1}
              OR
                [old_key_name] = {key2}
              OR
                [old_key_name] = {key3}
              OR
                [old_key_name] = {key4}
              OR
                [new_key_name] = {key5}
              OR
                [new_key_name] = {key6}
              OR
                [component] = {key7}';
        $params = array();
        $params['key1'] = '/last_update';
        $params['key2'] = '/gadgets/allowurl_items';
        $params['key3'] = '/config/frontend_ajaxed';
        $params['key4'] = '/config/controlpanel_name';
        $params['key5'] = 'pluggable';
        $params['key6'] = 'enabled';
        $params['key7'] = 'Registry';

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            _log(JAWS_LOG_ERROR, $res->getMessage());
            return $res;
        }

        return true;
    }

}