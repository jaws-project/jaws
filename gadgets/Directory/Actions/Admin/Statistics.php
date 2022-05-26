<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
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
        $this->app->layout->addLink('gadgets/Directory/Resources/style.css');
        $tpl = $this->gadget->template->loadAdmin('Statistics.html');
        $tpl->SetBlock('statistics');

        $tpl->SetVariable('title', $this::t('STATISTICS'));
        $tpl->SetVariable('lbl_files', $this::t('STAT_FILES'));
        $tpl->SetVariable('lbl_dirs', $this::t('STAT_DIRS'));

        $model = $this->gadget->model->loadAdmin('Statistics');
        $user = (int)$this->app->session->user->id;
        $stats = $model->GetStatistics($user);
        $tpl->SetVariable('files', $stats['files']);
        $tpl->SetVariable('dirs', $stats['dirs']);

        $tpl->ParseBlock('statistics');
        return $tpl->Get();
    }
}