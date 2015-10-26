<?php
/**
 * Emblems Gadget
 *
 * @category   GadgetLayout
 * @package    Emblems
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_Actions_Emblems extends Jaws_Gadget_Action
{
    /**
     * Displays the emblems
     *
     * @access  public
     * @return  string   XHTML UI
     */
    function Display()
    {
        $tpl = $this->gadget->template->load('Emblems.html');
        $tpl->SetBlock('emblems');
        $tpl->SetVariable('title', _t('EMBLEMS_ACTION_TITLE'));

        $model = $this->gadget->model->load('Emblems');
        $emblems = $model->GetEmblems(true);
        if (!Jaws_Error::IsError($emblems)) {
            $site   = urlencode(Jaws_Utils::getBaseURL('/', false));
            $page   = urlencode(Jaws_Utils::getRequestURL(false));
            $name   = urlencode($this->gadget->registry->fetch('site_name', 'Settings'));
            $slogan = urlencode($this->gadget->registry->fetch('site_slogan', 'Settings'));
            $title  = $GLOBALS['app']->Layout->GetTitle();

            foreach ($emblems as $e) {
                $e['url'] = str_replace(
                    array('{url}', '{base_url}', '{requested_url}', '{site_name}', '{site_slogan}', '{title}'),
                    array($page, $site, $page, $name, $slogan, $title),
                    $e['url']
                );
                $tpl->SetBlock('emblems/emblem');
                $tpl->SetVariable('id', $e['id']);
                $tpl->SetVariable('title', _t('EMBLEMS_TYPE_' . $e['type'], $e['title']));
                $tpl->SetVariable('image', $GLOBALS['app']->getDataURL('emblems/' . $e['image']));
                $tpl->SetVariable('url',   $e['url']);
                $tpl->ParseBlock('emblems/emblem');
            }
        }

        $tpl->ParseBlock('emblems');
        return $tpl->Get();
    }
}