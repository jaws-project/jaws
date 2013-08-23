<?php
/**
 * LinkDump Gadget
 *
 * @category    Gadget
 * @package     LinkDump
 * @author      Amir Mohammad Saied <amirsaied@gmail.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Actions_Display extends Jaws_Gadget_HTML
{
    /**
     * Get Display action params
     *
     * @access  public
     * @return  array list of Display action params
     */
    function DisplayLayoutParams()
    {
        $result = array();
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model', 'Groups');
        $groups = $model->GetGroups();
        if (!Jaws_Error::isError($groups)) {
            $pgroups = array();
            foreach ($groups as $group) {
                $pgroups[$group['id']] = $group['title'];
            }

            $result[] = array(
                'title' => _t('LINKDUMP_ACTIONS_DISPLAY'),
                'value' => $pgroups
            );
        }

        return $result;
    }

    /**
     * Display links
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  string  XHTML template content
     */
    function Display($gid = 0)
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model', 'Groups');
        $group = $model->GetGroup($gid);
        if (Jaws_Error::IsError($group) || empty($group)) {
            return false;
        }

        $data = array();
        $data['gid']  = $group['id'];
        $data['name'] = $group['title'];
        $data['title'] = _t('LINKDUMP_NAME');
        $data['lbl_clicks'] = _t('LINKDUMP_LINKS_CLICKS');
        $data['lst_simple'] = $group['link_type'] == 0;
        $target = $this->gadget->registry->fetch('links_target');
        $data['target'] = ($target == 'blank')? '_blank' : '_self';
        $feedname = empty($group['fast_url'])?
                    $GLOBALS['app']->UTF8->str_replace(' ', '-', $group['title']) : $group['fast_url'];
        $feedname = preg_replace('/[@?^=%&:;\/~\+# ]/i', '\1', $feedname);
        $data['linkdump_rdf'] = $GLOBALS['app']->getDataURL("xml/linkdump.$feedname.rdf", false);
        $data['feed'] = _t('LINKDUMP_LINKS_FEED');
        $gid = empty($group['fast_url']) ? $group['id'] : $group['fast_url'];
        $data['archive_url'] = $this->gadget->urlMap('Archive', array('id' => $gid));
        $data['links'] = $model->GetGroupLinks($group['id'], $group['limit_count'], $group['order_type']);
        if (!Jaws_Error::IsError($data['links'])) {
            foreach ($data['links'] as $indx => $link) {
                if ($group['link_type'] == 2) {
                    $lid = empty($link['fast_url'])? $link['id'] : $link['fast_url'];
                    $data['links'][$indx]['url'] = $this->gadget->urlMap('Link', array('id' => $lid));
                } else {
                    $data['links'][$indx]['url'] = $link['url'];
                }
            }
        }

        $tpl = $this->gadget->loadTemplate('LinkDump.html');
        return $tpl->fetch($data);
    }
}