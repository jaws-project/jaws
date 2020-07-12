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
        if (response['type'] == 'alert-success') {
            unselectGridRow('logs_datagrid');
            getDG('logs_datagrid', $('#logs_datagrid')[0].getCurrentPage(), true);
        }
    },
    DeleteLogsUseFilters: function(response) {
        if (response['type'] == 'alert-success') {
            unselectGridRow('logs_datagrid');
            getDG('logs_datagrid', $('#logs_datagrid')[0].getCurrentPage(), true);
        }
    },
    SaveSettings: function(response) {
        //
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
        'user'      : $('#filter_user').combobox('selectedItem').value,
        'priority'  : $('#filter_priority').val(),
        'result'    : $('#filter_result').val(),
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
        'user'      : $('#filter_user').combobox('selectedItem').value,
        'priority'  : $('#filter_priority').val(),
        'result'    : $('#filter_result').val(),
        'status'    : $('#filter_status').val()
    };

    if (combo.val() == 'delete') {
        if (rows.length < 1) {
            return;
        }

        var confirmation = confirm(jaws.Logs.Defines.confirmLogsDelete);
        if (confirmation) {
            LogsAjax.callAsync('DeleteLogs', rows);
        }
    } else if (combo.val() == 'deleteAll') {
        var confirmation = confirm(jaws.Logs.Defines.confirmLogsDelete);
        if (confirmation) {
            LogsAjax.callAsync('DeleteLogsUseFilters', {'filters':null});
        }
    } else if (combo.val() == 'deleteFiltered') {
        var confirmation = confirm(jaws.Logs.Defines.confirmLogsDelete);
        if (confirmation) {
            LogsAjax.callAsync('DeleteLogsUseFilters', {'filters':filters});
        }
    } else if (combo.val() == 'export') {
        window.location= LogsAjax.baseScript + '?reqGadget=Logs&reqAction=ExportLogs';
    } else if (combo.val() == 'exportFiltered') {
        var queryString = '&from_date=' + filters.from_date;
        queryString += '&to_date=' + filters.to_date;
        queryString += '&gname=' + filters.gadget;
        queryString += '&user=' + filters.user;
        queryString += '&priority=' + filters.priority;
        queryString += '&result=' + filters.result;
        queryString += '&status=' + filters.status;
        window.location= LogsAjax.baseScript + '?reqGadget=Logs&reqAction=ExportLogs' + queryString;
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
    $('#log_result').html(result['result']);
    $('#log_status').html(result['status']);
    $('#log_apptype').html(result['apptype']);
    $('#log_auth').html(result['auth']);
    $('#log_username').html('<a href = "' + result['user_url'] + '">' + result['username'] + '</a>');
    $('#log_ip').html(result['ip']);
    $('#log_agent').html(result['agent']);
    $('#log_date').html(result['time']);
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

/**
 * initiate User Filter
 */
function initiateUserFilter()
{
    $('#filter_user').combobox({
        'filterOnKeypress': true,
        'showOptionsOnKeypress': true,
        'noMatchesMessage': jaws.Logs.Defines.msgNoMatches
    });
    $('#filter_user').on('changed.fu.combobox', $.proxy(function (evt, data) {
        this.changeUserFilter(data.text);
    }, this));
}

/**
 * change User Filter
 */
function changeUserFilter(term) {
    if (term == '') {
        return false;
    }
    $('#filter_user ul').empty().append(
        $('<li>').append($('<a>').attr('href', '#').text( jaws.Logs.Defines.lbl_all_users)).attr('data-value', 0)
    );

    LogsAjax.callAsync(
        'GetUser',
        {'username': term},
        function (response, status) {
            console.log(response);
            if (response['type'] == 'alert-success' && response['data'] != null) {
                $("#filter_user ul").append(
                    $('<li>').append($('<a>').attr('href', '#').text(response['data'].username)).attr('data-value', response['data'].id)
                );
                searchLogs();
            }
        }
    );
}


$(document).ready(function() {
    switch (jaws.Defines.mainAction) {
        case 'Logs':
            $('#gadgets_filter').selectedIndex = 0;
            initDatePicker('from_date');
            initDatePicker('to_date');
            initDataGrid('logs_datagrid', LogsAjax, getLogs);
            initiateUserFilter();
            break;

        case 'Settings':
            break;
    }
});

var LogsAjax = new JawsAjax('Logs', LogsCallback);
cacheContactForm = null;
pillboxProcessing = null;