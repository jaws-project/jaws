<?php
/**
 * LinkDump Admin Gadget
 *
 * @category   Gadget
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Actions_Admin_Links extends Jaws_Gadget_Action
{
    /**
     * Links List Action
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  string  XHTML template content
     */
    function GetLinksList($gid)
    {
        $model = $this->gadget->model->load('Groups');
        $links = $model->GetGroupLinks($gid);
        if (Jaws_Error::IsError($links) || empty($links)) {
            return '';
        }

        $tpl = $this->gadget->template->loadAdmin('LinkDump.html');
        $tpl->SetBlock('linkdump');

        foreach ($links as $link) {
            $tpl->SetBlock('linkdump/link_list');
            $tpl->SetVariable('lid', 'link_'.$link['id']);
            $tpl->SetVariable('icon', 'gadgets/LinkDump/Resources/images/logo.mini.png');
            $tpl->SetVariable('title', $link['title']);
            $tpl->SetVariable('js_edit_func', "editLink(this, {$link['id']})");
            $tpl->SetVariable('add_icon', STOCK_NEW);
            $tpl->ParseBlock('linkdump/link_list');
        }

        $tpl->ParseBlock('linkdump');
        return $tpl->Get();
    }

    /**
     * Show a form to edit a given group
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GetLinkUI()
    {
        $tpl = $this->gadget->template->loadAdmin('LinkDump.html');
        $tpl->SetBlock('linkdump');
        $tpl->SetBlock('linkdump/LinksUI');

        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups();
        $groupCombo =& Piwi::CreateWidget('Combo', 'gid');
        $groupCombo->SetID('gid');
        $groupCombo->setStyle('width: 356px;');
        foreach ($groups as $group) {
            $groupCombo->AddOption($group['title'], $group['id']);
        }
        $groupCombo->AddEvent(ON_CHANGE, 'setRanksCombo(this.value);');

        $tpl->SetVariable('lbl_gid', $this::t('GROUPS_GROUP'));
        $tpl->SetVariable('gid', $groupCombo->Get());

        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $titleEntry->SetStyle('width: 356px;');
        $tpl->SetVariable('title', $titleEntry->Get());

        $tpl->SetVariable('lbl_url', Jaws::t('URL'));
        $urlEntry =& Piwi::CreateWidget('Entry', 'url', 'http://');
        $urlEntry->SetStyle('direction: ltr;width: 356px;');
        $tpl->SetVariable('url', $urlEntry->Get());

        $tpl->SetVariable('lbl_fast_url', $this::t('FASTURL'));
        $fasturl =& Piwi::CreateWidget('Entry', 'fast_url', '');
        $fasturl->SetStyle('direction: ltr; width: 356px;');
        $tpl->SetVariable('fast_url', $fasturl->Get());

        $linkdesc =& Piwi::CreateWidget('TextArea', 'description', '');
        $linkdesc->SetRows(4);
        $linkdesc->SetStyle('width: 356px;');
        $tpl->SetVariable('desc', $linkdesc->Get());
        $tpl->SetVariable('lbl_desc', Jaws::t('DESCRIPTION'));

        $rank =& Piwi::CreateWidget('Combo', 'rank');
        $rank->SetID('rank');
        $rank->setStyle('width: 128px;');
        $tpl->SetVariable('lbl_rank', $this::t('RANK'));
        $tpl->SetVariable('rank', $rank->Get());

        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $tpl->SetBlock('linkdump/LinksUI/tags');
            $tpl->SetVariable('lbl_tag', $this::t('LINKS_TAGS'));
            $linktags =& Piwi::CreateWidget('Entry', 'tags', '');
            $linktags->SetStyle('direction: ltr; width: 356px;');
            $tpl->SetVariable('tag', $linktags->Get());
            $tpl->ParseBlock('linkdump/LinksUI/tags');
        }

        $tpl->SetVariable('lbl_clicks', $this::t('LINKS_CLICKS'));
        $linkclicks  =& Piwi::CreateWidget('Entry', 'clicks', '');
        $linkclicks->SetEnabled(false);
        $linkclicks->SetStyle('direction: ltr; width: 128px;');
        $tpl->SetVariable('clicks', $linkclicks->Get());

        $tpl->ParseBlock('linkdump/LinksUI');
        $tpl->ParseBlock('linkdump');
        return $tpl->Get();
    }
}