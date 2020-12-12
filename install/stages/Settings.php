<?php
/**
 * Sets up the default site settings.
 *
 * @category    Application
 * @package     InstallStage
 * @author      Jon Wood <jon@substance-it.co.uk>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2005-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Installer_Settings extends JawsInstaller
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
    protected function __construct($stage, $config = null)
    {
        parent::__construct($stage, $config);

        $this->_Fields = array(
            'site_name'     => 'Jaws ' . JAWS_VERSION,
            'site_slogan'   => JAWS_VERSION_CODENAME,
            'site_language' => $_SESSION['install']['language'],
            'site_sample'   => false,
        );

        require_once ROOT_JAWS_PATH . 'include/Jaws.php';
        if (!Jaws::getInstance(false)) {
            require_once ROOT_JAWS_PATH . 'include/Jaws/DB.php';
            // Connect to the database and setup registry and similar.
            $objDatabase = Jaws_DB::getInstance('default', $_SESSION['install']['Database']);

            // Create application
            $jawsApp = Jaws::getInstance();
            $jawsApp->registry->init();
            $jawsApp->loadPreferences(array('language' => $_SESSION['install']['language']), false);
            Jaws_Translate::getInstance()->LoadTranslation('Install', JAWS_COMPONENT_INSTALL);
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
        $request = Jaws_Request::getInstance();
        $post = $request->fetch($keys, 'post');
        foreach ($this->_Fields as $key => $value) {
            if ($post[$key] !== null) {
                $values[$key] = $post[$key];
            }
        }

        $data = array();
        if (isset($_SESSION['install']['data']['Settings'])) {
            $data = $_SESSION['install']['data']['Settings'];
        }

        $tpl = new Jaws_Template(false, false);
        $tpl->Load('display.html', 'stages/Settings/templates');
        $tpl->SetBlock('Settings');

        $tpl->setVariable('lbl_info',           $this->t('SETTINGS_INFO'));
        $tpl->setVariable('lbl_site_name',      $this->t('SETTINGS_SITE_NAME'));
        $tpl->setVariable('site_name_info',     $this->t('SETTINGS_SITE_NAME_INFO'));
        $tpl->setVariable('lbl_site_slogan',    $this->t('SETTINGS_SLOGAN'));
        $tpl->setVariable('site_slogan_info',   $this->t('SETTINGS_SLOGAN_INFO'));
        $tpl->setVariable('lbl_site_language',  $this->t('SETTINGS_SITE_LANGUAGE'));
        $tpl->setVariable('site_language_info', $this->t('SETTINGS_SITE_LANGUAGE_INFO'));
        $tpl->setVariable('lbl_site_sample',    $this->t('SETTINGS_SITE_SAMPLE'));
        $tpl->setVariable('site_sample_info',   $this->t('SETTINGS_SITE_SAMPLE_INFO'));
        $tpl->SetVariable('next',               Jaws::t('NEXT'));

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
        $request = Jaws_Request::getInstance();
        $post = $request->fetch($keys, 'post');

        if (isset($_SESSION['install']['data']['Settings'])) {
            $post = $_SESSION['install']['data']['Settings'] + $post;
        }

        _log(JAWS_DEBUG,"Setting up main settings (site name, description, languages, copyrights, etc");
        $settings = array();
        $settings['site_name']      = $post['site_name'];
        $settings['site_slogan']    = $post['site_slogan'];
        $settings['site_author']    = $_SESSION['install']['CreateUser']['nickname'];
        $settings['copyright']      = date('Y'). ', '. $post['site_name'];
        $settings['site_language']  = $post['site_language'];
        $settings['admin_language'] = $post['site_language'];
        $settings['site_email']     = $_SESSION['install']['CreateUser']['email'];
        foreach ($settings as $key => $value) {
            if (in_array($key, array('site_language', 'admin_language'))) {
                Jaws::getInstance()->registry->update($key, $value, true, 'Settings');
            } else {
                Jaws::getInstance()->registry->update($key, $value, false, 'Settings');
            }
        }

        if (!empty($post['site_sample'])) {
            // install sample gadgets/data
            $this->InstallSampleSite();
            
        }

        return true;
    }

    /**
     * Install some gadgets with default data
     *
     * @access  public
     * @return  bool|Jaws_Error  Either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function InstallSampleSite()
    {
        $gadgets = array('Blog', 'Phoo', 'LinkDump', 'Contact', 'Menu', 'Emblems');

        $variables = array();
        $variables['Blog'] = array (
            'timestamp' => Jaws_DB::getInstance()->date(),
            'blog_content1_title' => $this->t('SAMPLE_BLOG_CONTENT1_TITLE'),
            'blog_content1_summary' => $this->t('SAMPLE_BLOG_CONTENT1_SUMMARY'),
        );
        $variables['Phoo'] = array (
            'timestamp' => Jaws_DB::getInstance()->date(),
            'folder-path' => gmdate('Y_m_d'),
            'siteurl' => Jaws_Utils::getBaseURL('/', false),
        );
        $variables['LinkDump'] = array (
            'timestamp' => Jaws_DB::getInstance()->date(),
            'linkdump_title1' => $this->t('SAMPLE_LINKDUMP_TITLE1'),
            'linkdump_title2' => $this->t('SAMPLE_LINKDUMP_TITLE2'),
            'linkdump_title3' => $this->t('SAMPLE_LINKDUMP_TITLE3'),
        );
        $variables['Contact'] = array ();
        $variables['Menu'] = array (
            'timestamp' => Jaws_DB::getInstance()->date(),
            'siteurl'   => Jaws_Utils::getBaseURL('/', false),
            'menu_title1' => $this->t('SAMPLE_MENU_TITLE1'),
            'menu_title2' => $this->t('SAMPLE_MENU_TITLE2'),
            'menu_title3' => $this->t('SAMPLE_MENU_TITLE3'),
            'menu_title4' => $this->t('SAMPLE_MENU_TITLE4'),
        );
        $variables['Emblems'] = array ();

        // Install gadgets
        foreach ($gadgets as $gadget) {
            $objGadget = Jaws_Gadget::getInstance($gadget);
            if (Jaws_Error::IsError($objGadget)) {
                _log(JAWS_DEBUG, "There was a problem while loading sample gadget: $gadget");
                _log(JAWS_DEBUG, $objGadget->getMessage());
            } else {
                $installer = $objGadget->installer->load();
                $input_schema = ROOT_JAWS_PATH. "install/stages/Settings/Sample/$gadget/insert.xml";
                if (!file_exists($input_schema)) {
                    $input_schema = '';
                }
                $res = $installer->InstallGadget($input_schema, $variables[$gadget]);
                if (Jaws_Error::IsError($res)) {
                    _log(JAWS_DEBUG, "There was a problem while installing sample gadget $gadget");
                    _log(JAWS_DEBUG, $res->getMessage());
                } else {
                    _log(JAWS_DEBUG, "Sample gadget $gadget installed successfully.");
                }
            }
        }

        // Inserts layout sample itemes
        $objGadget = Jaws_Gadget::getInstance('Layout');
        if (Jaws_Error::IsError($objGadget)) {
            _log(JAWS_DEBUG, "There was a problem while loading gadget: Layout");
            _log(JAWS_DEBUG, $objGadget->getMessage());
        } else {
            $base_schema  = ROOT_JAWS_PATH. "gadgets/Layout/Resources/schema/schema.xml";
            $input_schema = ROOT_JAWS_PATH. "install/stages/Settings/Sample/Layout/insert.xml";

            $installer = $objGadget->installer->load();
            $res = $installer->installSchema($input_schema, '', $base_schema, true);
            if (Jaws_Error::IsError($res)) {
                _log(JAWS_DEBUG, "There was a problem while inserting sample itemes into gadget $gadget");
                _log(JAWS_DEBUG, $res->getMessage());
            } else {
                _log(JAWS_DEBUG,"Sample itemes inserted into gadget $gadget.");
            }
        }

        // set Blog as main gadget
        Jaws::getInstance()->registry->update('main_gadget', 'Blog', true, 'Settings');

        // Copy Photo Organizer sample data
        $source = ROOT_JAWS_PATH. 'install/stages/Settings/Sample/Phoo/data/';
        $destination = ROOT_DATA_PATH. 'phoo/'. $variables['Phoo']['folder-path']. '/';
        if (Jaws_Utils::copy($source, $destination)) {
            _log(JAWS_DEBUG, "Sample data of gadget Phoo copied successfully.");
        } else {
            _log(JAWS_DEBUG, "There was a problem while copying sample data of gadget Phoo");
        }

        return true;
    }

}