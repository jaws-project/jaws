<?php
/**
 * Report Stage
 *
 * @category   Application
 * @package    UpgradeStage
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_Report extends JawsUpgrader
{
    /**
     * Builds the upgrader page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display the status of old/current jaws versions
     */
    function Display()
    {
        include_once ROOT_JAWS_PATH.'include/Jaws/DB.php';
        $objDatabase = Jaws_DB::getInstance('default', $_SESSION['upgrade']['Database']);

        require_once ROOT_JAWS_PATH . 'include/Jaws.php';
        $jawsApp = Jaws::getInstance();

        if (!isset($_SESSION['upgrade']['InstalledVersion'])) {
            $_SESSION['upgrade']['InstalledVersion'] = $jawsApp->registry->init();
        }
        $jawsApp->loadPreferences(array('language' => $_SESSION['upgrade']['language']), false);

        $supportedversions = array(
            array('version' => '1.9.0', 'stage' => '16'),
            array('version' => '1.8.0', 'stage' => '15'),
            array('version' => '1.7.0', 'stage' => '14'),
            array('version' => '1.6.0', 'stage' => '13'),
            array('version' => '1.5.0', 'stage' => '12'),
            array('version' => '1.4.0', 'stage' => '11'),
            array('version' => '1.3.0', 'stage' => '10'),
            array('version' => '1.2.0', 'stage' => '9'),
            array('version' => '1.1.1', 'stage' => '8'),
            array('version' => '1.1.0', 'stage' => '7'),
            array('version' => '1.0.0', 'stage' => '6'),
            array('version' => '0.9.3', 'stage' => null),
            array('version' => '0.9.2', 'stage' => null),
            array('version' => '0.9.1', 'stage' => null),
            array('version' => '0.9.0', 'stage' => null),
        );

        _log(JAWS_DEBUG,"Checking/Reporting previous missed installations");
        $tpl = new Jaws_Template(false, false);
        $tpl->Load('display.html', 'stages/Report/templates');
        $tpl->SetBlock('Report');

        $tpl->setVariable('lbl_info',    $this->t('REPORT_INFO', JAWS_VERSION));
        $tpl->setVariable('lbl_message', $this->t('REPORT_MESSAGE'));
        $tpl->SetVariable('next',        Jaws::t('NEXT'));

        $versions_to_upgrade = 0;
        $_SESSION['upgrade']['stagedVersions'] = array();
        foreach($supportedversions as $supported) {
            $tpl->SetBlock('Report/versions');
            $tpl->SetBlock('Report/versions/version');
            $tpl->SetVariable('description', $supported['version']);

            $_SESSION['upgrade']['versions'][$supported['version']] = array(
                'version' => $supported['version'],
                'stage' =>   $supported['stage'],
                'file' =>    (isset($supported['file'])? $supported['file'] : ''),
                'script' =>  (isset($supported['script'])? $supported['script'] : '')
            );

            if (version_compare($supported['version'], $_SESSION['upgrade']['InstalledVersion'], '<=')) {
                if ($supported['version'] == JAWS_VERSION) {
                    $tpl->SetVariable('status', $this->t('REPORT_NO_NEED_CURRENT'));
                    _log(JAWS_DEBUG,$supported['version']." does not requires upgrade(is current)");
                } else {
                    $tpl->SetVariable('status', $this->t('REPORT_NO_NEED'));
                    _log(JAWS_DEBUG,$supported['version']." does not requires upgrade");
                }
                $_SESSION['upgrade']['versions'][$supported['version']]['status'] = true;
            } else {
                $tpl->SetVariable('status', $this->t('REPORT_NEED'));
                $_SESSION['upgrade']['versions'][$supported['version']]['status'] = false;
                $versions_to_upgrade++;
                _log(JAWS_DEBUG,$supported['version']." requires upgrade");
                $_SESSION['upgrade']['versions'][$supported['version']]['status'] = false;
            }

            if (!is_null($supported['stage'])) {
                $_SESSION['upgrade']['stagedVersions'][] = $supported['version'];
            }

            $tpl->ParseBlock('Report/versions/version');
            $tpl->ParseBlock('Report/versions');
        }
        $_SESSION['upgrade']['versions_to_upgrade'] = $versions_to_upgrade;
        arsort($_SESSION['upgrade']['versions']);
        krsort($_SESSION['upgrade']['stagedVersions']);

        $tpl->ParseBlock('Report');
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
        if (version_compare($_SESSION['upgrade']['InstalledVersion'], '0.9.0' , '<')) {
            return Jaws_Error::raiseError($this->t('REPORT_NOT_SUPPORTED'), 0, JAWS_ERROR_WARNING);
        }

        if (is_dir(ROOT_DATA_PATH. "languages")) {
            // transform customized translated files
            $rootfiles = array('Global.php', 'Date.php', 'Install.php', 'Upgrade.php');
            $languages = scandir(ROOT_DATA_PATH. 'languages');
            foreach ($languages as $lang) {
                if($lang == '.' || $lang == '..') {
                    continue;
                }

                $ostr = "define('_".strtoupper($lang).'_';
                $nstr = "define('_".strtoupper($lang).'_DATA_';

                // gadgets
                if (is_dir(ROOT_DATA_PATH. "languages/$lang/gadgets")) {
                    $lGadgets = scandir(ROOT_DATA_PATH. "languages/$lang/gadgets");
                    foreach ($lGadgets as $lGadget) {
                        if($lGadget == '.' || $lGadget == '..') {
                            continue;
                        }

                        $fstring = @file_get_contents(ROOT_DATA_PATH. "languages/$lang/gadgets/$lGadget");
                        $fstring = strtr($fstring, array($nstr => $nstr, $ostr => $nstr));
                        @file_put_contents(ROOT_DATA_PATH. "languages/$lang/gadgets/$lGadget", $fstring);
                    }
                }

                // plugins
                if (is_dir(ROOT_DATA_PATH. "languages/$lang/plugins")) {
                    $lPlugins = scandir(ROOT_DATA_PATH. "languages/$lang/plugins");
                    foreach ($lPlugins as $lPlugin) {
                        if($lPlugin == '.' || $lPlugin == '..') {
                            continue;
                        }

                        $fstring = @file_get_contents(ROOT_DATA_PATH. "languages/$lang/plugins/$lPlugin");
                        $fstring = strtr($fstring, array($nstr => $nstr, $ostr => $nstr));
                        @file_put_contents(ROOT_DATA_PATH. "languages/$lang/plugins/$lPlugin", $fstring);
                    }
                }
            }

            // others
            foreach ($rootfiles as $rfile) {
                if (Jaws_FileManagement_File::file_exists(ROOT_DATA_PATH. "languages/$lang/$rfile")) {
                    $fstring = @file_get_contents(ROOT_DATA_PATH. "languages/$lang/$rfile");
                    $fstring = strtr($fstring, array($nstr => $nstr, $ostr => $nstr));
                    @file_put_contents(ROOT_DATA_PATH. "languages/$lang/$rfile", $fstring);
                }
            }
        }

        foreach($_SESSION['upgrade']['stagedVersions'] as $stagedVersion) {
            if (!$_SESSION['upgrade']['versions'][$stagedVersion]['status']) {
                if ($_SESSION['upgrade']['stage'] < $_SESSION['upgrade']['versions'][$stagedVersion]['stage']) {
                    return true;
                } else {
                    $_SESSION['upgrade']['stage']++;
                }
            } else {
                $_SESSION['upgrade']['stage']++;
            }
        }

        return true;
    }

}