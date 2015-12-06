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
class Directory_Actions_Admin_Statistics extends Jaws_Gadget_Action
{
    /**
     * Displays overal information about files and directory
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Statistics()
    {
        $GLOBALS['app']->Layout->AddHeadLink('gadgets/Directory/Resources/style.css');
        $tpl = $this->gadget->template->loadAdmin('Statistics.html');
        $tpl->SetBlock('statistics');

        $tpl->SetVariable('title', _t('DIRECTORY_STATISTICS'));
        $tpl->SetVariable('lbl_files', _t('DIRECTORY_STAT_FILES'));
        $tpl->SetVariable('lbl_dirs', _t('DIRECTORY_STAT_DIRS'));

        $model = $this->gadget->model->loadAdmin('Statistics');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $stats = $model->GetStatistics($user);
        $tpl->SetVariable('files', $stats['files']);
        $tpl->SetVariable('dirs', $stats['dirs']);

        $tpl->ParseBlock('statistics');
        return $tpl->Get();
    }
}