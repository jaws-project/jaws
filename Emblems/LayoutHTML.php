<?php
/**
 * Emblems Gadget (layout side)
 *
 * @category   GadgetLayout
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class EmblemsLayoutHTML 
{
    /**
     * Displays the emblems in our site
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function Display()
    {
        $tpl = new Jaws_Template('gadgets/Emblems/templates/');
        $tpl->Load('Emblems.html');
        $model = $GLOBALS['app']->LoadGadget('Emblems', 'Model');
        $rsemblem = $model->GetEmblems(true);
        if (!Jaws_Error::IsError($rsemblem)) {
            $rows = $this->GetRegistry('rows');
            $cols = ceil(count($rsemblem) / $rows);
            $tpl->SetBlock('emblems');
            $tpl->SetVariable('title', _t('EMBLEMS_ACTION_TITLE'));
            $cell = 0;
            $siteURL = Jaws_Utils::getRequestURL(false);
            for ($i = 0; $i < $rows; $i++) {
                $tpl->SetBlock('emblems/emblemrow');
                for ($j = 0; $j < $cols; $j++) {
                    if ($cell < count($rsemblem)) {
                        $tpl->SetBlock('emblems/emblemrow/emblem');
                        $e = $rsemblem[$cell];
                        $tpl->SetVariable('id', $e['id']);
                        if ($this->GetRegistry('allow_url') == 'true') {
                            $tpl->SetBlock('emblems/emblemrow/emblem/url');
                            $tpl->SetVariable('src', $GLOBALS['app']->getDataURL('emblems/' . $e['src']));
                            $e['url'] = str_replace('{url}', $siteURL, $e['url']);
                            $tpl->SetVariable('url', $e['url']);
                            switch($e['emblem_type']) {
                            case 'B':
                                $tpl->SetVariable('rel', 'external');
                                break;
                            case 'L':
                                $tpl->SetVariable('rel', 'license');
                                break;
                            case 'V':
                                $tpl->SetVariable('rel', 'validation');
                                break;
                            case 'S':
                                $tpl->SetVariable('rel', 'external');
                                break;
                            case 'P':
                                $tpl->SetVariable('rel', 'powered');
                                break;
                            }
                            $tpl->SetVariable('title', $model->TranslateType($e['emblem_type']) . $e['title']);
                            $tpl->ParseBlock('emblems/emblemrow/emblem/url');
                        } else{
                            $tpl->SetBlock('emblems/emblemrow/emblem/normal');
                            $tpl->SetVariable('src', $GLOBALS['app']->getDataURL('emblems/' . $e['src']));
                            $tpl->SetVariable('title', $model->TranslateType($e['emblem_type']) . $e['title']);
                            $tpl->ParseBlock('emblems/emblemrow/emblem/normal');
                        }
                        $cell++;
                        $tpl->ParseBlock('emblems/emblemrow/emblem');
                    }
                }
                $tpl->ParseBlock('emblems/emblemrow');
            }
            $tpl->ParseBlock('emblems');
        }

        return $tpl->Get();
    }

}