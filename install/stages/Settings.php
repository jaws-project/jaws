<?php
/**
 * Sets up the default site settings.
 *
 * @category    Application
 * @package     InstallStage
 * @author      Jon Wood <jon@substance-it.co.uk>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Installer_Settings extends JawsInstallerStage
{
    /**
     * Default values
     *
     * @access private
     * @var array
     */
    var $_Fields = array();

    /**
     * Constructor
     *
     * @access public
     */
    function Installer_Settings()
    {
        $this->_Fields = array(
            'site_name'     => 'Jaws ' . JAWS_VERSION,
            'site_slogan'   => JAWS_VERSION_CODENAME,
            'site_language' => $_SESSION['install']['language'],
            'site_sample'   => false,
        );

        if (!isset($GLOBALS['app'])) {
            // Connect to the database and setup registry and similar.
            require_once JAWS_PATH . 'include/Jaws/DB.php';
            $GLOBALS['db'] = new Jaws_DB($_SESSION['install']['Database']);
            // Create application
            include_once JAWS_PATH . 'include/Jaws.php';
            $GLOBALS['app'] = new Jaws();
            $GLOBALS['app']->Registry->Init();
            $GLOBALS['app']->loadPreferences(array('language' => $_SESSION['install']['language']));
        }
    }

    /**
     * Builds the installer page.
     *
     * @access  public
     * @return  string      A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $values = $this->_Fields;
        $keys = array_keys($values);
        $request =& Jaws_Request::getInstance();
        $post = $request->get($keys, 'post');
        foreach ($this->_Fields as $key => $value) {
            if ($post[$key] !== null) {
                $values[$key] = $post[$key];
            }
        }

        $data = array();
        if (isset($_SESSION['install']['data']['Settings'])) {
            $data = $_SESSION['install']['data']['Settings'];
        }

        $tpl = new Jaws_Template();
        $tpl->Load('display.html', 'stages/Settings/templates');
        $tpl->SetBlock('Settings');

        $tpl->setVariable('lbl_info',           _t('INSTALL_SETTINGS_INFO'));
        $tpl->setVariable('lbl_site_name',      _t('INSTALL_SETTINGS_SITE_NAME'));
        $tpl->setVariable('site_name_info',     _t('INSTALL_SETTINGS_SITE_NAME_INFO'));
        $tpl->setVariable('lbl_site_slogan',    _t('INSTALL_SETTINGS_SLOGAN'));
        $tpl->setVariable('site_slogan_info',   _t('INSTALL_SETTINGS_SLOGAN_INFO'));
        $tpl->setVariable('lbl_site_language',  _t('INSTALL_SETTINGS_SITE_LANGUAGE'));
        $tpl->setVariable('site_language_info', _t('INSTALL_SETTINGS_SITE_LANGUAGE_INFO'));
        $tpl->setVariable('lbl_site_sample',    _t('INSTALL_SETTINGS_SITE_SAMPLE'));
        $tpl->setVariable('site_sample_info',   _t('INSTALL_SETTINGS_SITE_SAMPLE_INFO'));
        $tpl->SetVariable('next',               _t('GLOBAL_NEXT'));

        $tpl->SetVariable('site_name',     $values['site_name']);
        $tpl->SetVariable('site_slogan',   $values['site_slogan']);
        // fill languages combo box
        $languages = Jaws_Utils::GetLanguagesList();
        foreach ($languages as $k => $v) {
            $tpl->SetBlock('Settings/lang');
            $tpl->setVariable('code', $k);
            $tpl->setVariable('title', $v);
            if ($values['site_language'] == $k) {
                $tpl->setVariable('selected', 'selected="selected"');
            } else {
                $tpl->setVariable('selected', '');
            }
            $tpl->ParseBlock('Settings/lang');
        }

        $tpl->ParseBlock('Settings');
        return $tpl->Get();
    }

    /**
     * Validates any data provided to the stage.
     *
     * @access  public
     * @return  bool|Jaws_Error  Returns either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function Validate()
    {
        return true;
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
        $keys = array_keys($this->_Fields);
        $request =& Jaws_Request::getInstance();
        $post = $request->get($keys, 'post');

        if (isset($_SESSION['install']['data']['Settings'])) {
            $post = $_SESSION['install']['data']['Settings'] + $post;
        }

        _log(JAWS_LOG_DEBUG,"Setting up main settings (site name, description, languages, copyrights, etc");
        $settings = array();
        $settings['site_name']      = $post['site_name'];
        $settings['site_slogan']    = $post['site_slogan'];
        $settings['site_author']    = $_SESSION['install']['CreateUser']['nickname'];
        $settings['copyright']      = date('Y'). ', '. $post['site_name'];
        $settings['site_language']  = $post['site_language'];
        $settings['admin_language'] = $post['site_language'];
        $settings['site_email']     = $_SESSION['install']['CreateUser']['email'];
        foreach ($settings as $key => $value) {
            $GLOBALS['app']->Registry->update($key, $value, 'Settings');
        }

        if (!empty($post['site_sample'])) {
            // install sample gadgets/data
            
        }

        return true;
    }
}