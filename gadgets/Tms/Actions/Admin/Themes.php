<?php
/**
 * TMS (Theme Management System) Gadget Admin view
 *
 * @category   GadgetAdmin
 * @package    TMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Tms_Actions_Admin_Themes extends Jaws_Gadget_Action
{
    /**
     * Calls Themes function
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Admin()
    {
        return $this->Themes();
    }

    /**
     * Prepares the menubar
     *
     * @access  public
     * @param   string  $action  Selected action
     * @return  string  XHTML menubar
     */
    function Menubar($action)
    {
        $actions = array('Themes');
        if (!in_array($action, $actions)) {
            $action = 'Themes';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Themes', _t('TMS_THEMES'),
                            BASE_SCRIPT . '?gadget=Tms&amp;action=Themes',
                            'gadgets/Tms/Resources/images/themes.png');
        $menubar->Activate($action);
        return $menubar->Get();
    }

    /**
     * Builds themes management UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Themes()
    {
        $this->AjaxMe('script.js');

        $model = $this->gadget->loadModel();
        $tpl = $this->gadget->loadAdminTemplate('Themes.html');
        $tpl->SetBlock('Tms');

        $tpl->SetVariable('confirmUninstallTheme', _t('TMS_THEMES_UNINSTALL_CONFIRM'));
        $tpl->SetVariable('noAvailableData', _t('TMS_THEMES_NOTHING'));

        $themesCombo =& Piwi::CreateWidget('ComboGroup', 'themes_combo');
        $themesCombo->SetID('themes_combo');
        $themesCombo->SetSize(24);
        $themesCombo->addGroup('local', _t('LAYOUT_THEME_LOCAL'));
        $themesCombo->addGroup('remote', _t('LAYOUT_THEME_REMOTE'));
        $themesCombo->AddEvent(ON_CHANGE, 'javascript: editTheme(this.value);');
        $themes = Jaws_Utils::GetThemesList();
        foreach ($themes as $theme => $tInfo) {
            $themesCombo->AddOption($tInfo['local']? 'local' : 'remote', $tInfo['name'], $theme);
        }
        $tpl->SetVariable('themes_combo', $themesCombo->Get());

        if ($this->gadget->GetPermission('UploadTheme')) {
            // Upload theme
            $tpl->SetBlock('Tms/UploadTheme');
            $fileEntry =& Piwi::CreateWidget('FileEntry', 'theme_upload');
            $fileEntry->SetStyle('width: 250px;');
            $fileEntry->AddEvent(ON_CHANGE, 'javascript: uploadTheme();');
            $tpl->SetVariable('lbl_theme_upload', _t('TMS_UPLOAD_THEME'));
            $tpl->SetVariable('theme_upload', $fileEntry->Get());
            $tpl->ParseBlock('Tms/UploadTheme');
        }

        $btnDownload =& Piwi::CreateWidget('Button',
                                           'download_button',
                                           _t('TMS_DOWNLOAD'), 
                                           STOCK_DOWN);
        $btnDownload->AddEvent(ON_CLICK, 'javascript: downloadTheme();');
        $btnDownload->SetStyle('display: none');
        $tpl->SetVariable('btn_download', $btnDownload->Get());

        $tpl->SetVariable('menubar', $this->Menubar('Admin'));
        $tpl->ParseBlock('Tms');

        return $tpl->Get();
    }

    /**
     * Builds an XHTML UI of the theme information
     *
     * @access  public
     * @param   string  $theme  Name of the theme
     * @return  string  XHTML content
     */
    function GetThemeInfo($theme)
    {
        $tpl = $this->gadget->loadAdminTemplate('ThemeInfo.html');
        $tpl->SetBlock('ThemeInfo');
        $tpl->SetVariable('theme_str', _t('TMS_THEME_INFO_NAME'));

        $themes = Jaws_Utils::GetThemesList();
        if (isset($themes[$theme])) {
            $tInfo = $themes[$theme];
            $tpl->SetVariable('theme_name', $tInfo['name']);
            $tpl->SetVariable('download',
                              ($tInfo['local'] ||
                               (isset($tInfo['download']) && (bool)$tInfo['download']))? 'true' : 'false');
            $tpl->SetVariable('theme_image', $tInfo['image']);
            $tpl->SetBlock('ThemeInfo/section');
            $tpl->SetVariable('name', _t('TMS_THEME_INFO_DESCRIPTION'));
            if (empty($tInfo['desc'])) {
                $tpl->SetVariable('value', _t('TMS_THEME_INFO_DESCRIPTION_DEFAULT'));
            } else {
                $tpl->SetVariable('value', $tInfo['desc']);
            }
            $tpl->ParseBlock('ThemeInfo/section');

            //We have authors?
            if (count($tInfo['authors']) > 0) {
                $tpl->SetBlock('ThemeInfo/multisection');
                $tpl->SetVariable('name', _t('TMS_THEME_INFO_AUTHOR'));
                foreach($tInfo['authors'] as $author) {
                    $tpl->SetBlock('ThemeInfo/multisection/subsection');
                    $tpl->SetVariable('value', $author);
                    $tpl->ParseBlock('ThemeInfo/multisection/subsection');
                }
                $tpl->ParseBlock('ThemeInfo/multisection');
            }
        } else {
            $tpl->SetVariable('downloadable', 'false');
            $tpl->SetBlock('ThemeInfo/error');
            $tpl->SetVariable('msg', $tInfo->getMessage());
            $tpl->ParseBlock('ThemeInfo/error');
        }

        $tpl->ParseBlock('ThemeInfo');
        return $tpl->Get();
    }

    /**
     * Uploads a new theme
     *
     * @access  public
     * @return  void
     */
    function UploadTheme()
    {
        $this->gadget->CheckPermission('UploadTheme');

        $res = Jaws_Utils::ExtractFiles($_FILES, JAWS_DATA . 'themes' . DIRECTORY_SEPARATOR, false);
        if (!Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('TMS_THEME_UPLOADED'), RESPONSE_NOTICE);
        } else {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Tms&action=Themes');
    }

    /**
     * Downloads the theme
     *
     * @access  public
     * @returns void
     */
    function DownloadTheme()
    {
        $theme = jaws()->request->fetch('theme', 'get');
        $themes = Jaws_Utils::GetThemesList();
        if (isset($themes[$theme])) {
            $locally = $themes[$theme]['local'];
            if (!$locally) {
                if (!isset($themes[$theme]['download']) || !(bool)$themes[$theme]['download']) {
                    echo Jaws_HTTPError::Get(403);
                    exit;
                }
            }
            $tmpDir = sys_get_temp_dir();
            $tmsModel = $this->gadget->loadAdminModel('Themes');
            $res = $tmsModel->packTheme($theme,
                                        ($locally? JAWS_DATA : JAWS_BASE_DATA) . 'themes',
                                        $tmpDir,
                                        false);
            if (!Jaws_Error::isError($res)) {
                Jaws_Utils::Download($res, "$theme.zip");
            }
        }
    }

}