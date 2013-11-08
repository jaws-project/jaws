<?php
/**
 * Emblems Gadget
 *
 * @category   GadgetLayout
 * @package    Emblems
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2013 Jaws Development Group
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
        $tpl = $this->gadget->loadTemplate('Emblems.html');
        $tpl->SetBlock('emblems');
        $tpl->SetVariable('title', _t('EMBLEMS_ACTION_TITLE'));

        $model = $this->gadget->model->load('Emblems');
        $emblems = $model->GetEmblems(true);
        if (!Jaws_Error::IsError($emblems)) {
            $siteURL = Jaws_Utils::getRequestURL(false);
            foreach ($emblems as $e) {
                $tpl->SetBlock('emblems/emblem');
                $tpl->SetVariable('id', $e['id']);
                $tpl->SetVariable('title', _t('EMBLEMS_TYPE_' . $e['type'], $e['title']));
                $tpl->SetVariable('image', $GLOBALS['app']->getDataURL('emblems/' . $e['image']));
                $tpl->SetVariable('url', str_replace('{url}', rawurlencode($siteURL), $e['url']));
                $tpl->ParseBlock('emblems/emblem');
            }
        }

        $tpl->ParseBlock('emblems');
        return $tpl->Get();
    }
}