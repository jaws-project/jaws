<?php
/**
 * Jaws JSON Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Response_Print
{
    /**
     * Returns data in print template
     *
     * @access  public
     * @param   string  $data   Data string
     * @return  string  Returns encoded data
     */
    static function get($data)
    {
        // Set Headers
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        $jawsApp = Jaws::getInstance();
        // if current theme has a error code html file, return it, if not return the messages.
        $theme = $jawsApp->GetTheme();
        $site_name = $jawsApp->registry->fetch('site_name', 'Settings');
        if (file_exists($theme['path'] . 'Print.html')) {
            // fetch all registry keys related to site attributes
            $siteAttributes = $jawsApp->registry->fetchAll('Settings', false);

            $tpl = new Jaws_Template();
            $tpl->Load('Print.html', $theme['path']);
            $tpl->SetBlock('layout');

            //set global site configuration
            $tpl->SetVariable('encoding',    'utf-8');
            $tpl->SetVariable('site-name',   $site_name);
            $tpl->SetVariable('site-title',  $site_name);
            $tpl->SetVariable('site-direction',   Jaws::t('LANG_DIRECTION'));
            $tpl->SetVariable('site-slogan',      $siteAttributes['site_slogan']);
            $tpl->SetVariable('site-comment',     $siteAttributes['site_comment']);
            $tpl->SetVariable('site-author',      $siteAttributes['site_author']);
            $tpl->SetVariable('site-description', $siteAttributes['site_description']);
            $tpl->SetVariable('site-license',     $siteAttributes['site_license']);
            $tpl->SetVariable('site-copyright',   $siteAttributes['site_copyright']);

            $tpl->SetVariable(
                'page-title',
                !empty($jawsApp->mainAction)?
                    $jawsApp->mainAction->getAttribute($jawsApp->mainRequest['action'], 'title') :
                    $site_name
            );
            $tpl->SetVariable('page-content', $data);
            $tpl->ParseBlock('layout');
            return $tpl->Get();
        }

        return $data;
    }

}