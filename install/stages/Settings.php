<?php
/**
 * Sets up the default site settings.
 *
 * @author Jon Wood <jon@substance-it.co.uk>
 * @access public
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

        // Connect to the database and setup registry and similar.
        require_once JAWS_PATH . 'include/Jaws/DB.php';
        $GLOBALS['db'] = new Jaws_DB($_SESSION['install']['Database']);
        // Create application
        include_once JAWS_PATH . 'include/Jaws.php';
        $GLOBALS['app'] = new Jaws();
        $GLOBALS['app']->create();
        $GLOBALS['app']->OverwriteDefaults(array('language' => $_SESSION['install']['language']));
        $GLOBALS['app']->loadClass('ACL', 'Jaws_ACL');
    }

    /**
     * Sorts an array of Jaws_GadgetInfo objects by name.
     *
     * @access protected
     * @param  Jaws_GadgetInfo   $a
     * @param  Jaws_GadgetInfo   $b
     * @return int          1 = $a > $b, -1 = $b > $a, 0 = $a == $b
     */
    function SortGadgets($a, $b)
    {
        if ($a->GetName() == $b->GetName()) {
            return 0;
        }

        return ($a->GetName() < $b->GetName()) ? -1 : 1;
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
        include_once JAWS_PATH . 'include/Jaws/GadgetInfo.php';
        $model = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');

        $gdt =& Piwi::CreateWidget('Combo', 'default_gadget');
        $gdt->SetID('default_gadget');
        $gdt->AddOption(_t('GLOBAL_NOGADGET'), '');
        foreach ($model->GetGadgetsList(false, null, true) as $g => $tg) {
            $gdt->AddOption($tg['realname'], $g);
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
        $settings['/config/site_status']      = 'enabled';
        $settings['/config/site_name']        = $post['site_name'];
        $settings['/config/site_slogan']      = $post['site_slogan'];
        $settings['/config/site_comment']     = '';
        $settings['/config/site_keywords']    = '';
        $settings['/config/site_description'] = '';
        $settings['/config/custom_meta']      = '';
        $settings['/config/site_author']      = $_SESSION['install']['CreateUser']['nickname'];
        $settings['/config/site_license']     = '';
        $settings['/config/site_favicon']     = 'images/jaws.png';
        $settings['/config/title_separator']  = '-';
        $settings['/config/main_gadget']      = $post['default_gadget'];
        $settings['/config/copyright']        = date('Y') . ', ' . $_SESSION['install']['CreateUser']['name'];
        $settings['/config/site_language']    = $post['site_language'];
        $settings['/config/admin_language']   = $post['site_language'];
        $settings['/config/site_email']       = $_SESSION['install']['CreateUser']['email'];
        $settings['/config/cookie/domain']    = '';
        foreach ($settings as $key => $value) {
            $GLOBALS['app']->Registry->NewKey($key, $value);
        }

        // Commit the changes
        _log(JAWS_LOG_DEBUG,"Saving settings changes");
        $GLOBALS['app']->Registry->commit('core');

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