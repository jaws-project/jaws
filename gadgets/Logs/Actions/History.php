<?php
/**
 * Logs Gadget
 *
 * @category    Gadget
 * @package     Logs
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Logs_Actions_History extends Jaws_Gadget_Action
{
    /**
     * Get AboutUser action params(superadmin users list)
     *
     * @access  public
     * @return  array list of AboutUser action params(superadmin users list)
     */
    function LoginHistoryLayoutParams()
    {
        $result[] = array(
            'title' => _t('GLOBAL_COUNT'),
            'value' => 5
        );

        return $result;
    }

    /**
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function LoginHistory($limit = 5)
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return false;
        }

        $logModel = Jaws_Gadget::getInstance('Logs')->model->load('Logs');
        $logs = $logModel->GetLogs(
            array(
                'gadget' => 'Users',
                'action' => 'Login',
                'user'   => $GLOBALS['app']->Session->GetAttribute('user'),
            ),
            $limit
        );
        if (Jaws_Error::IsError($logs) || empty($logs)) {
            return false;
        }

        $tpl = $this->gadget->template->load('LoginHistory.html');
        $tpl->SetBlock('history');
        $date = Jaws_Date::getInstance();
        $tpl->SetVariable('title', _t('LOGS_LOGIN_HISTORY'));
        foreach ($logs as $log) {
            $tpl->SetBlock('history/'. $log['status']);
            $tpl->SetVariable('ip', long2ip($log['ip']));
            $tpl->SetVariable('agent', $log['agent']);
            $tpl->SetVariable('status_title', _t('GLOBAL_HTTP_ERROR_TITLE_'. $log['status']));
            $tpl->SetVariable('date', $date->Format($log['insert_time'], 'DN d MN Y H:i'));
            
            $tpl->ParseBlock('history/'. $log['status']);
        }

        $tpl->ParseBlock('history');
        return $tpl->Get();
    }

}