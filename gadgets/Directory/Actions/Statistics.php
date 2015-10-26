<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Actions_Statistics extends Jaws_Gadget_Action
{
    /**
     * Displays overal information about files and directory
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Statistics()
    {
        $GLOBALS['app']->Layout->AddHeadLink('gadgets/Directory/Resources/site_style.css');
        $tpl = $this->gadget->template->load('Statistics.html');
        $tpl->SetBlock('statistics');

        $tpl->SetVariable('title', _t('DIRECTORY_STATISTICS'));
        $tpl->SetVariable('lbl_files', _t('DIRECTORY_STAT_FILES'));
        $tpl->SetVariable('lbl_dirs', _t('DIRECTORY_STAT_DIRS'));
        $tpl->SetVariable('lbl_shared', _t('DIRECTORY_STAT_SHARED'));
        $tpl->SetVariable('lbl_foreign', _t('DIRECTORY_STAT_FOREIGN'));
        $tpl->SetVariable('lbl_public', _t('DIRECTORY_STAT_PUBLIC'));

        $model = $this->gadget->model->load('Statistics');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $stats = $model->GetStatistics($user);
        $tpl->SetVariable('files', $stats['files']);
        $tpl->SetVariable('dirs', $stats['dirs']);
        $tpl->SetVariable('shared', $stats['shared']);
        $tpl->SetVariable('foreign', $stats['foreign']);
        $tpl->SetVariable('public', $stats['public']);

        $tpl->ParseBlock('statistics');
        return $tpl->Get();
    }
}