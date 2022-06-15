<?php
/**
 * Logs Gadget
 *
 * @category    Gadget
 * @package     Logs
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2022 Jaws Development Group
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
            'title' => Jaws::t('COUNT'),
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
        if (!$this->app->session->user->logged) {
            return false;
        }

        $logModel = Jaws_Gadget::getInstance('Logs')->model->load('Logs');
        $logs = $logModel->GetLogs(
            array(
                'gadget' => 'Users',
                'action' => 'Login',
                'user'   => $this->app->session->user->id,
            ),
            $limit
        );
        if (Jaws_Error::IsError($logs) || empty($logs)) {
            return false;
        }

        foreach ($logs as &$log) {
            $log['ip'] = long2ip($log['ip']);
        }

        $assigns = array();
        $assigns['logs'] = $logs;

        return $this->gadget->template->xLoad('LoginHistory.html')->render($assigns);
    }

}