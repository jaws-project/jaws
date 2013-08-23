<?php
/**
 * LinkDump Gadget
 *
 * @category   Gadget
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Actions_Categories extends Jaws_Gadget_HTML
{
    /**
     * Display links categories
     *
     * @access  public
     * @return  XHTML template content
     */
    function ShowCategories()
    {
        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model', 'Groups');
        $groups = $model->GetGroups();
        if (Jaws_Error::IsError($group)) {
            return false;
        }

        $tpl = $this->gadget->loadTemplate('Categories.html');
        $tpl->SetBlock('categories');
        $tpl->SetVariable('title', _t('LINKDUMP_GROUPS'));

        foreach ($groups as $group) {
            $tpl->SetBlock('categories/item');
            $gid = empty($group['fast_url']) ? $group['id'] : $group['fast_url'];
            $tpl->SetVariable('url',   $GLOBALS['app']->Map->GetURLFor('LinkDump', 'Group', array('id' => $gid)));
            $tpl->SetVariable('title', $group['title']);
            $tpl->ParseBlock('categories/item');
        }

        $tpl->ParseBlock('categories');
        return $tpl->Get();
   }
}