<?php
/**
 * Sitemap Gadget
 *
 * @category   Gadget
 * @package    Sitemap
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2014 Jaws Development Group
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
                            _t('SITEMAP_TITLE'),
                            BASE_SCRIPT . '?gadget=Sitemap&amp;action=ManageSitemap',
                            'gadgets/Sitemap/Resources/images/logo.mini.png');
        $menubar->AddOption('Robots',
                            _t('SITEMAP_ROBOTS'),
                            BASE_SCRIPT . '?gadget=Sitemap&amp;action=Robots',
                            'gadgets/Sitemap/Resources/images/robots.png');
        $menubar->Activate($action);
        return $menubar->Get();
    }

}