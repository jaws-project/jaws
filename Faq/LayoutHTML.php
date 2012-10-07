<?php
/**
 * Faq Layout Gadget (layout actions)
 *
 * @category   GadgetLayout
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FaqLayoutHTML
{
    /**
     * Displays a list with links to each category
     *
     * @access       public
     * @return       template content
     */
    function ListCategories()
    {
        $tpl = new Jaws_Template('gadgets/Faq/templates/');
        $tpl->Load('Categories.html');
        $tpl->SetBlock('faq_categories');
        $tpl->SetVariable('title', _t('FAQ_CATEGORIES'));
        $model = $GLOBALS['app']->LoadGadget('Faq', 'Model');
        $cats = $model->GetCategories();
        if (is_array($cats) && count($cats) > 0) {
            foreach ($cats as $c) {
                $tpl->SetBlock('faq_categories/item');
                $tpl->SetVariable('id', $c['id']);
                $id = empty($c['fast_url']) ? $c['id'] : $c['fast_url'];
                $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Faq', 'ViewCategory', array('id' => $id)));
                $tpl->SetVariable('category',$c['category']);
                $tpl->ParseBlock('faq_categories/item');
            }
        }
        $tpl->ParseBlock('faq_categories');

        return $tpl->Get();
    }

}