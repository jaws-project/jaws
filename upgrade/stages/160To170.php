<?php
/**
 * Jaws Upgrade Stage - From 1.6.0 to 1.7.0
 *
 * @category    Application
 * @package     UpgradeStage
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_160To170 extends JawsUpgraderStage
{
    /**
     * Builds the upgrader page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $tpl = new Jaws_Template(false, false);
        $tpl->Load('display.html', 'stages/160To170/templates');
        $tpl->SetBlock('160To170');

        $tpl->setVariable('lbl_info',  _t('UPGRADE_VER_INFO', '1.6.0', '1.7.0'));
        $tpl->setVariable('lbl_notes', _t('UPGRADE_VER_NOTES'));
        $tpl->SetVariable('next',      _t('GLOBAL_NEXT'));

        $tpl->ParseBlock('160To170');
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
        $objDatabase = Jaws_DB::getInstance('default', $_SESSION['upgrade']['Database']);
        if (Jaws_Error::IsError($objDatabase)) {
            _log(
                JAWS_LOG_DEBUG,
                "There was a problem connecting to the database, please check the details and try again"
            );
            return new Jaws_Error(_t('UPGRADE_DB_RESPONSE_CONNECT_FAILED'), 0, JAWS_ERROR_WARNING);
        }

        $schema_array = array(
            '1.6.0' => '1.6.1',
            '1.6.1' => '1.6.2',
            '1.6.2' => '1.6.3',
            '1.6.3' => 'schema'
        );
        foreach ($schema_array as $old => $new) {
            // upgrade core database schema
            $old_schema = JAWS_PATH . "upgrade/Resources/schema/$old.xml";
            $new_schema = JAWS_PATH . "upgrade/Resources/schema/$new.xml";
            if (!file_exists($old_schema)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', "$old.xml"),0 , JAWS_ERROR_ERROR);
            }

            if (!file_exists($new_schema)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', "$new.xml"),0 , JAWS_ERROR_ERROR);
            }

            _log(JAWS_LOG_DEBUG,"Upgrading core schema");
            $result = Jaws_DB::getInstance()->installSchema($new_schema, array(), $old_schema);
            if (Jaws_Error::isError($result)) {
                _log(JAWS_LOG_ERROR, $result->getMessage());
                if ($result->getCode() !== MDB2_ERROR_ALREADY_EXISTS) {
                    return new Jaws_Error($result->getMessage(), 0, JAWS_ERROR_ERROR);
                }
            }

            // update sessions user/checksum
            if ($new == '1.6.1') {
                $objORM = Jaws_ORM::getInstance();
                $sessions = $objORM->table('session')->select('id:integer', 'user', 'data')->fetchAll();

                foreach ($sessions as $session) {
                    $data = unserialize($session['data']);
                    $newdata = array();
                    $userdata = array();
                    $sessiondata = array(
                        'auth'      => '',
                        'domain'    => '',
                        'type'      => '',
                        'longevity' => 0,
                        'webpush'   => '',
                    );

                    foreach ($data as $key => $value) {
                        $keyParts = explode('.', $key);
                        if (count($keyParts) == 3) {
                            if ($keyParts[1] == 'Response') {
                                $newdata[$keyParts[0]]['Response'.$keyParts[2]] = $value;
                            } else {
                                $newdata[$keyParts[0]][$keyParts[2]] = $value;
                            }
                        } else {
                            switch ($key) {
                                case 'user':
                                    $userdata['id'] = (int)$value;
                                    break;

                                case 'auth':
                                case 'domain':
                                    $userdata[$key] = $value;
                                    $sessiondata[$key] = $value;
                                    break;

                                case 'internal':
                                case 'username':
                                case 'superadmin':
                                case 'groups':
                                case 'logon_hours':
                                case 'expiry_date':
                                case 'concurrents':
                                case 'logged':
                                case 'layout':
                                case 'nickname':
                                case 'email':
                                case 'mobile':
                                case 'ssn':
                                case 'avatar':
                                    $userdata[$key] = $value;
                                    break;

                                case 'type':
                                case 'longevity':
                                case 'webpush':
                                    $sessiondata[$key] = $value;
                                    break;

                                default:
                                    $newdata[''][$key] = $value;
                            }

                        }
                    }

                    $userdata_serialized = serialize($userdata);
                    $checksum = md5((int)$session['user'] . $userdata_serialized);
                    $result = $objORM->update(
                        array(
                            'userid' => (int)$session['user'],
                            'user_attributes' => $userdata_serialized,
                            'data' => serialize($newdata),
                            'auth'      => $sessiondata['auth'],
                            'domain'    => $sessiondata['domain'],
                            'type'      => $sessiondata['type'],
                            'longevity' => $sessiondata['longevity'],
                            'webpush'   => $sessiondata['webpush'],
                            'checksum'  => $checksum
                        )
                    )->where('id', $session['id'])
                    ->exec();
                    if (Jaws_Error::IsError($result)) {
                        // do nothing
                    }
                }
            }
        }

        // Create application
        include_once JAWS_PATH . 'include/Jaws.php';
        Jaws::getInstance()->registry->init();

        // Upgrading core gadgets
        $gadgets = array('Settings', 'Users');
        foreach ($gadgets as $gadget) {
            $objGadget = Jaws_Gadget::getInstance($gadget);
            if (Jaws_Error::IsError($objGadget)) {
                _log(JAWS_LOG_DEBUG,"There was a problem loading core gadget: ".$gadget);
                return $objGadget;
            }

            $installer = $objGadget->installer->load();
            if (Jaws_Error::IsError($installer)) {
                _log(JAWS_LOG_DEBUG,"There was a problem loading installer of core gadget: $gadget");
                return $installer;
            }

            if (Jaws_Gadget::IsGadgetInstalled($gadget)) {
                $result = $installer->UpgradeGadget();
            } else {
                continue;
                //$result = $installer->InstallGadget();
            }

            if (Jaws_Error::IsError($result)) {
                _log(JAWS_LOG_DEBUG,"There was a problem installing/upgrading core gadget: $gadget");
                return $result;
            }
        }

        return true;
    }

}