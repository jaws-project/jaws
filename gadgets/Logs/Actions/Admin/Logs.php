<?php
/**
 * Logs Gadget
 *
 * @category     GadgetAdmin
 * @package     Logs
 * @author      HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @author      ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2008-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Logs_Actions_Admin_Logs extends Logs_Actions_Admin_Default
{
    /**
     *
     * @access  public
     * @return  string HTML content with menu and menu items
     */
    function Logs()
    {
        $this->AjaxMe('script.js');
        $this->gadget->define('confirmLogsDelete', $this::t('CONFIRM_DELETE'));
        $this->gadget->define('msgNoMatches', $this::t('COMBO_NO_MATCH_MESSAGE'));
        $this->gadget->define('noMatchesMessage', Jaws::t('COMBO_NO_MATCH_MESSAGE'));
        $this->gadget->define('datagridNoItems', Jaws::t('NOTFOUND'));

        $this->gadget->define('LANGUAGE', array(
            'gadget'=> Jaws::t('GADGETS'),
            'action'=> $this::t('ACTION'),
            'auth'=> Jaws::t('AUTHTYPE'),
            'username'=> Jaws::t('USERNAME'),
            'time'=> Jaws::t('DATE'),
            'view'=> Jaws::t('VIEW'),
            'delete'=> Jaws::t('DELETE')
        ));

        $gadgetList = Jaws_Gadget::getInstance('Components')->model->load('Gadgets')->GetGadgetsList();
        $gadgetList = Jaws_Error::IsError($gadgetList) ? array() : $gadgetList;
        $this->gadget->define('gadgetList', array_column($gadgetList, 'title', 'name'));

        $assigns = array();
        $assigns['menubar'] = empty($menubar) ? $this->MenuBar('Logs') : $menubar;
        $assigns['gadgets'] = $gadgetList;
        $assigns['from_date'] = $this->gadget->action->load('DatePicker')->xcalendar(array('name' => 'from_date'));
        $assigns['to_date'] = $this->gadget->action->load('DatePicker')->xcalendar(array('name' => 'to_date'));
        $assigns['priorityItems'] = array(
            JAWS_WARNING => $this::t('PRIORITY_5'),
            JAWS_NOTICE => $this::t('PRIORITY_6'),
            JAWS_INFO => $this::t('PRIORITY_7'),
        );
        $assigns['statusItems'] = array(
            1 => $this::t('LOG_STATUS_1'),
            2 => $this::t('LOG_STATUS_2'),
        );
        return $this->gadget->template->xLoadAdmin('Logs.html')->render($assigns);
    }

    /**
     * Return list of Logs data for use in datagrid
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetLogs()
    {
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );

        $model = $this->gadget->model->load('Logs');
        $logs = $model->GetLogs($post['filters'], $post['limit'], $post['offset']);
        if (Jaws_Error::IsError($logs)) {
            return $this->gadget->session->response($logs->GetMessage(), RESPONSE_ERROR);
        }

        $logsCount = $model->GetLogsCount($post['filters']);
        if (Jaws_Error::IsError($logsCount)) {
            return $this->gadget->session->response($logsCount->GetMessage(), RESPONSE_ERROR);
        }

        if ($logsCount > 0) {
            $objDate = Jaws_Date::getInstance();
            foreach ($logs as &$log) {
                $log['time'] = $objDate->Format($log['time'], 'yyyy/MM/dd HH:mm:ss');
            }
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $logsCount,
                'records' => $logs
            )
        );
    }

    /**
     * Return info of selected log
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetLog()
    {
        $id = $this->gadget->request->fetch('id');
        $logModel = $this->gadget->model->loadAdmin('Logs');
        $log = $logModel->GetLog($id);
        if (Jaws_Error::IsError($log)) {
            return $this->gadget->session->response($log->GetMessage(), RESPONSE_ERROR);
        }

        $date = Jaws_Date::getInstance();
        $log['time'] = $date->Format($log['time'], 'EEEE dd MMMM yyyy HH:mm:ss');
        $log['ip'] = long2ip($log['ip']);
        $log['priority'] = $this::t('PRIORITY_'. $log['priority']);
        $log['status']   = $this::t('LOG_STATUS_'. $log['status']);
        $log['backend']  = $log['backend']? $this::t('LOG_SCRIPT_ADMIN') : $this::t('LOG_SCRIPT_INDEX');

        // user's profile link
        $log['user_url'] = $this->app->map->GetMappedURL(
            'Users',
            'Profile',
            array('user' => $log['username'])
        );

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            $log
        );
    }

    /**
     * Delete Logs
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DeleteLogs()
    {
        $this->gadget->CheckPermission('DeleteLogs');
        $post = $this->gadget->request->fetch(array('ids:array', 'filters:array'), 'post');
        $logsID = $post['ids'];
        $filters = $post['filters'];
        $model = $this->gadget->model->loadAdmin('Logs');
        if (!empty($logsID)) {
            $res = $model->DeleteLogs($logsID);
        } else {
            $res = $model->DeleteLogsUseFilters($filters);
        }
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response($this::t('ERROR_CANT_DELETE_LOGS'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            $this::t('LOGS_DELETED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Export Logs
     *
     * @access  public
     * @return  void
     */
    function ExportLogs()
    {
        $this->gadget->CheckPermission('ExportLogs');

        $filters = $this->gadget->request->fetch(
            array('from_date', 'to_date', 'gadget', 'action', 'user', 'priority', 'result', 'status'),
            'get'
        );

        $model = $this->gadget->model->load('Logs');
        $logs = $model->GetLogs($filters);
        if (Jaws_Error::IsError($logs) || count($logs) < 1) {
            return;
        }

        $tmpDir = sys_get_temp_dir() . '/';
        $tmpCSVFileName = uniqid(rand(), true) . '.csv';
        $fp = fopen($tmpDir . $tmpCSVFileName, 'w');

        $date = Jaws_Date::getInstance();
        foreach ($logs as $log) {
            $exportData = '';
            $exportData .= $log['id'] . ',';
            $exportData .= $log['username'] . ',';
            $exportData .= $log['gadget'] . ',';
            $exportData .= $log['action'] . ',';
            $exportData .= $log['priority'] . ',';
            $exportData .= $log['apptype'] . ',';
            $exportData .= $log['backend'] . ',';
            $exportData .= long2ip($log['ip']) . ',';
            $exportData .= $log['result'] . ',';
            $exportData .= $log['status'] . ',';
            $exportData .= $date->Format($log['time'], 'yyyy-MM-dd HH:mm:ss');
            $exportData .= PHP_EOL;
            fwrite($fp, $exportData);
        }
        fclose($fp);

        require_once PEAR_PATH. 'File/Archive.php';
        $tmpFileName = uniqid(rand(), true) . '.tar.gz';
        $tmpArchiveName = $tmpDir . $tmpFileName;
        $writerObj = File_Archive::toFiles();
        $src = File_Archive::read($tmpDir . $tmpCSVFileName);
        $dst = File_Archive::toArchive($tmpArchiveName, $writerObj);
        $res = File_Archive::extract($src, $dst);
        if (!PEAR::isError($res)) {
            return Jaws_FileManagement_File::download($tmpArchiveName, $tmpFileName);
        }

        Jaws_Header::Referrer();
    }
}