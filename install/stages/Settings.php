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
            'site_name'        => 'Jaws ' . JAWS_VERSION,
            'site_slogan'      => JAWS_VERSION_CODENAME,
            'site_language'    => $_SESSION['install']['language'],
            'default_gadget'   => ''
        );

        if (!isset($GLOBALS['app'])) {
            // Connect to the database and setup registry and similar.
            require_once JAWS_PATH . 'include/Jaws/DB.php';
            $GLOBALS['db'] = new Jaws_DB($_SESSION['install']['Database']);
            // Create application
            include_once JAWS_PATH . 'include/Jaws.php';
            $GLOBALS['app'] = new Jaws();
            $GLOBALS['app']->create();
            $GLOBALS['app']->OverwriteDefaults(array('language' => $_SESSION['install']['language']));
            $GLOBALS['app']->loadObject('Jaws_ACL', 'ACL');
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

        define('PIWI_URL', 'libraries/piwi/');
        define('PIWI_CREATE_PIWIXML', 'no');
        define('PIWI_LOAD', 'SMART');
        require_once JAWS_PATH . 'libraries/piwi/Piwi.php';

        // Build the languages select.
        $lang =& Piwi::CreateWidget('Combo', 'site_language');
        $lang->SetID('site_language');
        $languages = Jaws_Utils::GetLanguagesList();
        foreach ($languages as $k => $v) {
            $lang->AddOption($v, $k);
        }
        $lang->SetDefault($values['site_language']);

        // Build the gadgets select.
        include_once JAWS_PATH . 'include/Jaws/Gadget.php';
        $model = $GLOBALS['app']->LoadGadget('Components', 'AdminModel');

        $gdt =& Piwi::CreateWidget('Combo', 'default_gadget');
        $gdt->SetID('default_gadget');
        $gdt->AddOption(_t('GLOBAL_NOGADGET'), '');
        $gadgets = $model->GetGadgetsList(null, null, null, null, true);
        foreach ($gadgets as $gadget => $tg) {
            $gdt->AddOption($tg['realname'], $gadget);
        }
        $gdt->SetDefault($values['default_gadget']);

        $tpl = new Jaws_Template('stages/Settings/templates/');
        $tpl->Load('display.html', false, false);
        $tpl->SetBlock('Settings');

        $tpl->setVariable('lbl_info',            _t('INSTALL_SETTINGS_INFO'));
        $tpl->setVariable('lbl_site_name',       _t('INSTALL_SETTINGS_SITE_NAME'));
        $tpl->setVariable('site_name_info',      _t('INSTALL_SETTINGS_SITE_NAME_INFO'));
        $tpl->setVariable('lbl_site_slogan',     _t('INSTALL_SETTINGS_SLOGAN'));
        $tpl->setVariable('site_slogan_info',    _t('INSTALL_SETTINGS_SLOGAN_INFO'));
        $tpl->setVariable('lbl_default_gadget',  _t('INSTALL_SETTINGS_DEFAULT_GADGET'));
        $tpl->setVariable('default_gadget_info', _t('INSTALL_SETTINGS_DEFAULT_GADGET_INFO'));
        $tpl->setVariable('lbl_site_language',   _t('INSTALL_SETTINGS_SITE_LANGUAGE'));
        $tpl->setVariable('site_language_info',  _t('INSTALL_SETTINGS_SITE_LANGUAGE_INFO'));
        $tpl->SetVariable('next',                _t('GLOBAL_NEXT'));

        $tpl->SetVariable('site_name',      $values['site_name']);
        $tpl->SetVariable('site_slogan',    $values['site_slogan']);
        $tpl->SetVariable('site_language',  $lang->Get());
        $tpl->SetVariable('default_gadget', $gdt->Get());

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
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('site_name'), 'post');

        if (isset($_SESSION['install']['data']['Settings'])) {
            $post = $_SESSION['install']['data']['Settings'] + $post;
        }

        if (!empty($post['site_name'])) {
            return true;
        }
        _log(JAWS_LOG_DEBUG,"Site name wasn't found");
        return new Jaws_Error(_t('INSTALL_USER_RESPONSE_SITE_NAME_EMPTY'), 0, JAWS_ERROR_WARNING);
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
        $settings['main_gadget']    = $post['default_gadget'];
        $settings['copyright']      = date('Y'). ', '. $post['site_name'];
        $settings['site_language']  = $post['site_language'];
        $settings['admin_language'] = $post['site_language'];
        $settings['site_email']     = $_SESSION['install']['CreateUser']['email'];
        foreach ($settings as $key => $value) {
            $GLOBALS['app']->Registry->update($key, $value, 'Settings');
        }

        require_once JAWS_PATH . 'include/Jaws/URLMapping.php';
        $GLOBALS['app']->Map = new Jaws_URLMapping();

        if (!empty($post['default_gadget'])) {
            $result = Jaws_Gadget::EnableGadget($post['default_gadget']);
            _log(JAWS_LOG_DEBUG,"Enabling ".$post['default_gadget']." gadget");
            if (Jaws_Error::IsError($result)) {
                _log(JAWS_LOG_DEBUG,$result->getMessage());
                return $result;
            }
            _log(JAWS_LOG_DEBUG,$post['default_gadget']." has been enabled");

        }

        return true;
    }
}