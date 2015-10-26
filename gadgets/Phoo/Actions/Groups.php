<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Groups extends Jaws_Gadget_Action
{
    /**
     * Displays phoo groups layout
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Groups()
    {
        $tpl = $this->gadget->template->load('Groups.html');
        $tpl->SetBlock('groups');
        $tpl->SetVariable('title', _t('PHOO_ALBUMS_GROUPS'));

        $gModel = $this->gadget->model->load('Groups');
        $groups = $gModel->GetGroups();
        if (is_array($groups)) {
            foreach ($groups as $group) {
                $url = $this->gadget->urlMap('AlbumList', array('group' => $group['id']));
                $tpl->SetBlock('groups/group');
                $tpl->SetVariable('url', $url);
                $tpl->SetVariable('name', $group['name']);
                $tpl->ParseBlock('groups/group');
            }
        }

        $tpl->ParseBlock('groups');
        return $tpl->Get();
    }
}