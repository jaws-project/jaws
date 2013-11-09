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
        if (response[0]['type'] == 'response_notice') {
            unselectGridRow('logs_datagrid');
            getDG('logs_datagrid');
        }
        showResponse(response);
    },
    SaveSettings: function(response) {
        showResponse(response);
    }
}

/**
 * On term key press, for compatibility Opera/IE with other browsers
 */
function OnTermKeypress(element, event)
{
    if (event.keyCode == 13) {
        element.blur();
        element.focus();
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
            'from_date' : $('from_date').value,
            'to_date'   : $('to_date').value,
            'gadget'    : $('filter_gadget').value,
            'user'      : $('filter_user').value,
            'priority'  : $('filter_priority').value,
            'term'      : $('filter_term').value
        }});

    if (reset) {
        $(name).setCurrentPage(0);
        var total = LogsAjax.callSync('GetLogsCount', {
            'from_date': $('from_date').value,
            'to_date': $('to_date').value,
            'gadget': $('filter_gadget').value,
            'user': $('filter_user').value,
            'priority': $('filter_priority').value,
            'term': $('filter_term').value
        });

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
 * Get selected log info
 *
 */
function viewLog(rowElement, id)
{
    selectGridRow('contacts_datagrid', rowElement.parentNode.parentNode);
    var result = LogsAjax.callSync('GetLog', {'id': id});
    $('log_title').innerHTML = result['title'];
    $('log_gadget').innerHTML = result['gadget'];
    $('log_action').innerHTML = result['action'];
    $('log_backend').innerHTML = result['backend'];
    $('log_priority').innerHTML = result['priority'];
    $('log_status').innerHTML = result['status'];
    $('log_apptype').innerHTML = result['apptype'];
    $('log_username').innerHTML = '<a href = "' + result['user_url'] + '">' + result['username'] + '</a>';
    $('log_nickname').innerHTML = result['nickname'];
    $('log_ip').innerHTML = result['ip'];
    $('log_agent').innerHTML = result['agent'];
    $('log_date').innerHTML = result['insert_time'];
}

/**
 * Search logs
 */
function searchLogs()
{
    getLogs('logs_datagrid', 0, true);
}

/**
 * save properties
 */
function saveSettings()
{
    LogsAjax.callAsync('SaveSettings', {
        'log_priority_level': $('priority').value,
        'log_parameters': $('log_parameters').value
    });
}

var LogsAjax = new JawsAjax('Logs', LogsCallback);
LogsAjax.backwardSupport();
cacheContactForm = null;