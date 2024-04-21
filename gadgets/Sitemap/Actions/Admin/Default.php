<?php
/**
 * Sitemap Gadget
 *
 * @category   Gadget
 * @package    Sitemap
 * @author     ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2014-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Actions_Admin_Default extends Jaws_Gadget_Action
{
    /**
     * Builds the users menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML menubar
     */
    function MenuBar($action)
    {
        $actions = array('Sitemap', 'Robots');
        if (!in_array($action, $actions)) {
            $action = 'Dashboard';
        }

        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('Sitemap',
                            $this::t('TITLE'),
                            BASE_SCRIPT . '?reqGadget=Sitemap&amp;reqAction=ManageSitemap',
                            'gadgets/Sitemap/Resources/images/logo.mini.png');
        $menubar->AddOption('Robots',
                            $this::t('ROBOTS'),
                            BASE_SCRIPT . '?reqGadget=Sitemap&amp;reqAction=Robots',
                            'gadgets/Sitemap/Resources/images/robots.png');
        $menubar->Activate($action);
        return $menubar->Get();
    }

}