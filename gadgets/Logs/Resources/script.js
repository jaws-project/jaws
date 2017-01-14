/**
 * Logs Javascript actions
 *
 * @category    Ajax
 * @package     Logs
 * @author      HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var LogsCallback = {
    DeleteLogs: function(response) {
        if (response[0]['type'] == 'alert-success') {
            unselectGridRow('logs_datagrid');
            getDG('logs_datagrid', $('#logs_datagrid')[0].getCurrentPage(), true);
        }
        LogsAjax.showResponse(response);
    },
    DeleteLogsUseFilters: function(response) {
        if (response[0]['type'] == 'alert-success') {
            unselectGridRow('logs_datagrid');
            getDG('logs_datagrid', $('#logs_datagrid')[0].getCurrentPage(), true);
        }
        LogsAjax.showResponse(response);
    },
    SaveSettings: function(response) {
        LogsAjax.showResponse(response);
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
function getLogs(name, offset, reset)
{
    var filters = {
        'from_date' : $('#from_date').val(),
        'to_date'   : $('#to_date').val(),
        'gadget'    : $('#filter_gadget').val(),
        'user'      : $('#filter_user').val(),
        'priority'  : $('#filter_priority').val(),
        'status'    : $('#filter_status').val()
    };

    var result = LogsAjax.callSync('GetLogs', {
        'offset': offset,
        'filters': filters
    });

    if (reset) {
        var total = LogsAjax.callSync('GetLogsCount', {
            'filters': filters
        });
    }
    resetGrid(name, result, total);
}

/**
 * Executes an action on logs
 */
function logsDGAction(combo)
{
    var rows = $('#logs_datagrid')[0].getSelectedRows();

    var filters = {
        'from_date' : $('#from_date').val(),
        'to_date'   : $('#to_date').val(),
        'gadget'    : $('#filter_gadget').val(),
        'user'      : $('#filter_user').val(),
        'priority'  : $('#filter_priority').val(),
        'status'    : $('#filter_status').val()
    };

    if (combo.val() == 'delete') {
        if (rows.length < 1) {
            return;
        }

        var confirmation = confirm(jaws.gadgets.Logs.confirmLogsDelete);
        if (confirmation) {
            LogsAjax.callAsync('DeleteLogs', rows);
        }
    } else if (combo.val() == 'deleteAll') {
        var confirmation = confirm(jaws.gadgets.Logs.confirmLogsDelete);
        if (confirmation) {
            LogsAjax.callAsync('DeleteLogsUseFilters', {'filters':null});
        }
    } else if (combo.val() == 'deleteFiltered') {
        var confirmation = confirm(jaws.gadgets.Logs.confirmLogsDelete);
        if (confirmation) {
            LogsAjax.callAsync('DeleteLogsUseFilters', {'filters':filters});
        }
    } else if (combo.val() == 'export') {
        window.location= LogsAjax.baseScript + '?gadget=Logs&action=ExportLogs';
    } else if (combo.val() == 'exportFiltered') {
        var queryString = '&from_date=' + filters.from_date;
        queryString += '&to_date=' + filters.to_date;
        queryString += '&gname=' + filters.gadget;
        queryString += '&user=' + filters.user;
        queryString += '&priority=' + filters.priority;
        queryString += '&status=' + filters.status;
        window.location= LogsAjax.baseScript + '?gadget=Logs&action=ExportLogs' + queryString;
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
    $('#log_gadget').html(result['gadget']);
    $('#log_action').html(result['action']);
    $('#log_backend').html(result['backend']);
    $('#log_priority').html(result['priority']);
    $('#log_status').html(result['status']);
    $('#log_apptype').html(result['apptype']);
    $('#log_username').html('<a href = "' + result['user_url'] + '">' + result['username'] + '</a>');
    $('#log_nickname').html(result['nickname']);
    $('#log_ip').html(result['ip']);
    $('#log_agent').html(result['agent']);
    $('#log_date').html(result['insert_time']);
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
    LogsAjax.callAsync(
        'SaveSettings', {
            'log_priority_level': $('#priority').val(),
            'log_parameters': $('#log_parameters').val()
        }
    );
}

$(document).ready(function() {
    switch (jaws.core.mainAction) {
        case 'Logs':
            $('#gadgets_filter').selectedIndex = 0;
            initDataGrid('logs_datagrid', LogsAjax, getLogs);
            break;

        case 'Settings':
            break;
    }
});

var LogsAjax = new JawsAjax('Logs', LogsCallback);
cacheContactForm = null;