<?php
/**
 * TMS (Theme Management System) Gadget Admin view
 *
 * @category   GadgetAdmin
 * @package    TMS
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Tms_Actions_Admin_Themes extends Jaws_Gadget_Action
{
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
        $menubar->AddOption('Themes', $this::t('THEMES'),
                            BASE_SCRIPT . '?reqGadget=Tms&amp;reqAction=Themes',
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
        $this->gadget->define('confirmDeleteTheme', $this::t('THEMES_DELETE_CONFIRM'));

        $tpl = $this->gadget->template->loadAdmin('Themes.html');
        $tpl->SetBlock('Tms');

        $themesCombo =& Piwi::CreateWidget('ComboGroup', 'themes_combo');
        $themesCombo->SetID('themes_combo');
        $themesCombo->SetSize(24);
        $themesCombo->addGroup(0, $this::t('LAYOUT.THEME_LOCAL'));
        $themesCombo->addGroup(1, $this::t('LAYOUT.THEME_REMOTE'));
        $themesCombo->AddEvent(ON_CHANGE, 'javascript:editTheme(this.value);');
        $themes = Jaws_Utils::GetThemesInfo();
        foreach ($themes[0] as $theme => $tInfo) {
            $themesCombo->AddOption(0, $tInfo['name'], "$theme,0");
        }
        foreach ($themes[1] as $theme => $tInfo) {
            $themesCombo->AddOption(1, $tInfo['name'], "$theme,1");
        }
        $tpl->SetVariable('themes_combo', $themesCombo->Get());

        if ($this->gadget->GetPermission('UploadTheme')) {
            // Upload theme
            $tpl->SetBlock('Tms/UploadTheme');
            $fileEntry =& Piwi::CreateWidget('FileEntry', 'theme_upload');
            $fileEntry->SetStyle('width: 250px;');
            $fileEntry->AddEvent(ON_CHANGE, 'javascript:uploadTheme();');
            $tpl->SetVariable('lbl_theme_upload', $this::t('UPLOAD_THEME'));
            $tpl->SetVariable('theme_upload', $fileEntry->Get());
            $tpl->ParseBlock('Tms/UploadTheme');
        }

        $btnDownload =& Piwi::CreateWidget('Button',
                                           'download_button',
                                           $this::t('DOWNLOAD'), 
                                           STOCK_DOWN);
        $btnDownload->AddEvent(ON_CLICK, 'javascript:downloadTheme();');
        $btnDownload->SetStyle('display: none');
        $tpl->SetVariable('btn_download', $btnDownload->Get());

        $btnDelete =& Piwi::CreateWidget('Button',
                                           'delete_button',
                                           Jaws::t('DELETE'),
                                           STOCK_DELETE);
        $btnDelete->AddEvent(ON_CLICK, 'javascript:deleteTheme();');
        $btnDelete->SetStyle('display: none');
        $tpl->SetVariable('btn_delete', $btnDelete->Get());

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
        $tpl = $this->gadget->template->loadAdmin('ThemeInfo.html');
        $tpl->SetBlock('ThemeInfo');
        $tpl->SetVariable('theme_str', $this::t('THEME_INFO_NAME'));

        @list($theme, $locality) = explode(',', $theme);
        $tInfo = Jaws_Utils::GetThemesInfo($locality, $theme);
        if (!empty($tInfo)) {
            $tpl->SetVariable('theme_name', $tInfo['name']);
            $tpl->SetVariable('download', (bool)$tInfo['download']? 'true' : 'false');
            // get default theme
            $defaultTheme = (array)$this->app->registry->fetch('theme', 'Settings');

            $tpl->SetVariable(
                'delete',
                ($locality == 0 && ($defaultTheme['locality']!=0 || $theme != $defaultTheme['name']))? 'true' : 'false'
            );

            $tpl->SetVariable('theme_image', $tInfo['image']);
            $tpl->SetBlock('ThemeInfo/section');
            $tpl->SetVariable('name', $this::t('THEME_INFO_DESCRIPTION'));
            if (empty($tInfo['desc'])) {
                $tpl->SetVariable('value', $this::t('THEME_INFO_DESCRIPTION_DEFAULT'));
            } else {
                $tpl->SetVariable('value', $tInfo['desc']);
            }
            $tpl->ParseBlock('ThemeInfo/section');

            //We have authors?
            if (count($tInfo['authors']) > 0) {
                $tpl->SetBlock('ThemeInfo/multisection');
                $tpl->SetVariable('name', $this::t('THEME_INFO_AUTHOR'));
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
            $tpl->SetVariable('msg', $this::t('ERROR_THEME_DOES_NOT_EXISTS'));
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

        $res = Jaws_FileManagement_File::extractFiles($_FILES, ROOT_DATA_PATH . 'themes/', false);
        if (!Jaws_Error::IsError($res)) {
            $this->gadget->session->push($this::t('THEME_UPLOADED'), RESPONSE_NOTICE);
        } else {
            $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
        }

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Tms&reqAction=Themes');
    }

    /**
     * Downloads the theme
     *
     * @access  public
     * @returns void
     */
    function DownloadTheme()
    {
        $theme = $this->gadget->request->fetch('theme', 'get');
        @list($theme, $locality) = explode(',', $theme);

        $tInfo = Jaws_Utils::GetThemesInfo($locality, $theme);
        if (!empty($tInfo)) {
            if (!$tInfo['download']) {
                return Jaws_HTTPError::Get(403);
            }

            $tmpDir = sys_get_temp_dir();
            $tmsModel = $this->gadget->model->loadAdmin('Themes');
            $res = $tmsModel->packTheme(
                $tInfo['name'],
                ($locality == 0? ROOT_DATA_PATH : JAWS_BASE_DATA) . 'themes',
                $tmpDir,
                false
            );
            if (!Jaws_Error::isError($res)) {
                Jaws_FileManagement_File::download($res, "$theme.zip");
                return;
            }
        } else {
            return Jaws_HTTPError::Get(404);
        }
    }

    /**
     * Delete the theme
     *
     * @access  public
     * @param   string  $theme
     * @returns boolean
     */
    function DeleteTheme($theme)
    {
        $this->gadget->CheckPermission('DeleteTheme');
        @list($theme, $locality) = explode(',', $theme);

        $tInfo = Jaws_Utils::GetThemesInfo($locality, $theme);
        if (!empty($tInfo) && $locality == 0) {
            // get default theme
            $defaultTheme = (array)$this->app->registry->fetch('theme', 'Settings');
            // Check is default theme?
            if (($defaultTheme['locality'] != 0) || ($theme != $defaultTheme['name'])) {
                return Jaws_FileManagement_File::delete(JAWS_THEMES . $theme);
            }
        }

        return false;
    }
}