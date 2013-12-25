<?php
/**
 * Layout Core Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Actions_Theme extends Jaws_Gadget_Action
{
    /**
     *
     *
     */
    function ChangeTheme()
    {
        $this->gadget->CheckPermission('ManageThemes');
        $theme = jaws()->request->fetch('theme', 'post');

        $layout_path = JAWS_THEMES. $theme;
        if (!file_exists($layout_path. '/layout.html')) {
            $layout_path = JAWS_BASE_THEMES. $theme;
        }
        $tpl = $this->gadget->template->loadAdmin("$layout_path/layout.html");

        // Validate theme
        if (!isset($tpl->Blocks['layout'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_NO_BLOCK', $theme, 'layout'), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout');
        }
        if (!isset($tpl->Blocks['layout']->InnerBlock['head'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_NO_BLOCK', $theme, 'head'), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout');
        }
        if (!isset($tpl->Blocks['layout']->InnerBlock['main'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_NO_BLOCK', $theme, 'main'), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout');
        }

        // Verify blocks/Reassign gadgets
        $model = $this->gadget->model->loadAdmin('Sections');
        $sections = $model->GetLayoutSections();
        foreach ($sections as $section) {
            if (!isset($tpl->Blocks['layout']->InnerBlock[$section])) {
                if (isset($tpl->Blocks['layout']->InnerBlock[$section . '_narrow'])) {
                    $model->MoveSection($section, $section . '_narrow');
                } elseif (isset($tpl->Blocks['layout']->InnerBlock[$section . '_wide'])) {
                    $model->MoveSection($section, $section . '_wide');
                } else {
                    if (strpos($section, '_narrow')) {
                        $clear_section = str_replace('_narrow', '', $section);
                    } else {
                        $clear_section = str_replace('_wide', '', $section);
                    }
                    if (isset($tpl->Blocks['layout']->InnerBlock[$clear_section])) {
                        $model->MoveSection($section, $clear_section);
                    } else {
                        $model->MoveSection($section, 'main');
                    }
                }
            }
        }

        $this->gadget->registry->updateByUser(
            'theme',
            $theme,
            'Settings',
            $GLOBALS['app']->Session->GetAttribute('layout')
        );
        $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_THEME_CHANGED'), RESPONSE_NOTICE);
        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout');
    }

}