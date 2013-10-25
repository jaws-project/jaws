/**
 * Logs Javascript actions
 *
 * @category   Ajax
 * @package    Logs
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var LogsCallback = {
    DeleteLogs: function(response) {
        if (response[0].type == 'response_notice') {
            unselectGridRow('logs_datagrid');
            getDG('logs_datagrid');
        }
        showResponse(response);
    }
}

/**
 * Get logs
 *
 */
function getLogs(name, offset, reset) {
    var result = LogsAjax.callSync('GetLogs', {
        'offset': offset,
        'filters': {
            'from_date': $('from_date').value,
            'to_date': $('to_date').value,
            'gadget': $('filter_gadget').value,
            'user': $('filter_user').value
        }});

    if (reset) {
        $(name).setCurrentPage(0);
        var total = LogsAjax.callSync('GetLogsCount', {
            'offset': {
                'from_date': $('from_date').value,
                'to_date': $('to_date').value,
                'gadget': $('filter_gadget').value,
                'user': $('filter_user').value
            }});

    }
    resetGrid(name, result, total);
}

/**
 * Executes an action on logs
 */
function logsDGAction(combo)
{
    var rows = $('logs_datagrid').getSelectedRows();
    if (rows.length < 1) {
        return;
    }

    if (combo.value == 'delete') {
        var confirmation = confirm(confirmLogsDelete);
        if (confirmation) {
            LogsAjax.callAsync('DeleteLogs', rows);
        }
    }
}

/**
 * Delete a log
 *
 */
function deleteLog(logID)
{
    var confirmation = confirm(confirmLogsDelete);
    if (confirmation) {
        var logsId = new Array();
        logsId[0] = logID;
        LogsAjax.callAsync('DeleteLogs', logsId);
    }
}

/**
 * Get selected log info
 *
 */
function viewLog(logID)
{
    var result = LogsAjax.callSync('GetLogInfo', {'logID': logID});
    $('log_gadget').innerHTML   = result['gadget'];
    $('log_action').innerHTML   = result['action'];
    $('log_user').innerHTML     = '<a href = "' + result['user_url'] + '">' + result['username'] + '</a>';
    $('log_ip').innerHTML       = result['ip'];
    $('log_agent').innerHTML    = result['agent'];
    $('log_date').innerHTML     = result['insert_time'];
}

/**
 * Search logs
 */
function searchLogs()
{
    getLogs('logs_datagrid', 0, true);
}

var LogsAjax = new JawsAjax('Logs', LogsCallback);
LogsAjax.backwardSupport();
cacheContactForm = null;