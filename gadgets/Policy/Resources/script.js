/**
 * Policy Javascript actions
 *
 * @category   Ajax
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var PolicyCallback = {
    AddIPRange: function(response) {
        if (response[0]['type'] == 'alert-success') {
            $('#blocked_ips_datagrid')[0].addItem();
            $('#blocked_ips_datagrid')[0].setCurrentPage(0);
            getDG();
            stopAction();
        }
        PolicyAjax.showResponse(response);
    },

    EditIPRange: function(response) {
        if (response[0]['type'] == 'alert-success') {
            stopAction();
            getDG();
        }
        PolicyAjax.showResponse(response);
    },

    DeleteIPRange: function(response) {
        if (response[0]['type'] == 'alert-success') {
            $('#blocked_ips_datagrid')[0].deleteItem();
            getDG();
        }
        PolicyAjax.showResponse(response);
    },

    AddAgent: function(response) {
        if (response[0]['type'] == 'alert-success') {
            $('#blocked_agents_datagrid')[0].addItem();
            $('#blocked_agents_datagrid')[0].setCurrentPage(0);
            getDG();
            stopAction();
        }
        PolicyAjax.showResponse(response);
    },

    EditAgent: function(response) {
        if (response[0]['type'] == 'alert-success') {
            stopAction();
            getDG();
        }
        PolicyAjax.showResponse(response);
    },

    DeleteAgent: function(response) {
        if (response[0]['type'] == 'alert-success') {
            $('#blocked_agents_datagrid')[0].deleteItem();
            getDG();
        }
        PolicyAjax.showResponse(response);
    },

    IPBlockingBlockUndefined: function(response) {
        PolicyAjax.showResponse(response);
    },

    AgentBlockingBlockUndefined: function(response) {
        PolicyAjax.showResponse(response);
    },

    UpdateEncryptionSettings: function(response) {
        PolicyAjax.showResponse(response);
    },

    UpdateAntiSpamSettings: function(response) {
        PolicyAjax.showResponse(response);
    },

    UpdateAdvancedPolicies: function(response) {
        PolicyAjax.showResponse(response);
    }
};

/**
 * Select DataGrid row
 *
 */
function selectDataGridRow(rowElement)
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRowColor = rowElement.style.backgroundColor;
    rowElement.style.backgroundColor = '#ffffcc';
    selectedRow = rowElement;
}

/**
 * Unselect DataGrid row
 *
 */
function unselectDataGridRow()
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRow = null;
    selectedRowColor = null;
}

function toggleCaptcha(field)
{
    if ($('#' + field + '_captcha').val() == 'DISABLED') {
        $('#' + field + '_captcha_driver').prop('disabled', true);
    } else {
        $('#' + field + '_captcha_driver').prop('disabled', false);
    }
}

/**
 * Add/Edit Blocked a IP Range
 */
function saveIPRange()
{
    if (!$('#from_ipaddress').val()) {
        alert(jaws.Policy.Defines.incompleteFields);
        return false;
    }

    if ($('#id').val() == 0) {
        PolicyAjax.callAsync(
            'AddIPRange', [
                $('#from_ipaddress').val(),
                $('#to_ipaddress').val(),
                $('#script').val(),
                $('#blocked').val()
            ]
        );
    } else {
        PolicyAjax.callAsync(
            'EditIPRange', [
                $('#id').val(),
                $('#from_ipaddress').val(),
                $('#to_ipaddress').val(),
                $('#script').val(),
                $('#blocked').val()
            ]
        );
    }
}

/**
 * Edit an IP range
 *
 */
function editIPRange(element, id)
{
    currentAction = 'IPBlocking';
    selectDataGridRow($(element).parent().parent()[0]);
    var ipRange = PolicyAjax.callSync('GetIPRange', id);

    $('#id').val(ipRange['id']);
    $('#from_ipaddress').val(ipRange['from_ip']);
    $('#to_ipaddress').val(ipRange['to_ip']);
    $('#script').val(ipRange['script']);
    $('#blocked').prop('selectedIndex', ipRange['blocked']? 1 : 0);
}

/**
 * Delete an IP range
 */
function deleteIPRange(element, id)
{
    stopAction();
    selectDataGridRow($(element).parent().parent()[0]);
    var answer = confirm(jaws.Policy.Defines.confirmIPRangeDelete);
    if (answer) {
        PolicyAjax.callAsync('DeleteIPRange', id);
    }
    unselectDataGridRow();
}

/**
 * Add/Edit Blocked Agent
 */
function saveAgent()
{
    if (!$('#agent').val()) {
        alert(jaws.Policy.Defines.incompleteFields);
        return false;
    }

    if ($('#id').val() == 0) {
        PolicyAjax.callAsync('AddAgent', [$('#agent').val(), $('#script').val(), $('#blocked').val()]);
    } else {
        PolicyAjax.callAsync(
            'EditAgent',
            [$('#id').val(), $('#agent').val(), $('#script').val(), $('#blocked').val()]
        );
    }
}

/**
 * Edit a Agent
 *
 */
function editAgent(element, id)
{
    currentAction = 'AgentBlocking';
    selectDataGridRow($(element).parent().parent()[0]);
    var agent = PolicyAjax.callSync('GetAgent', id);

    $('#id').val(agent['id']);
    $('#agent').val(agent['agent'].defilter());
    $('#script').val(agent['script'].defilter());
    $('#blocked').prop('selectedIndex', agent['blocked']? 1 : 0);
}

/**
 * Delete an Agent
 */
function deleteAgent(element, id)
{
    stopAction();
    selectDataGridRow($(element).parent().parent()[0]);
    var answer = confirm(jaws.Policy.Defines.confirmAgentDelete);
    if (answer) {
        PolicyAjax.callAsync('DeleteAgent', id);
    }
    unselectDataGridRow();
}

/**
 * setIPBlockAnonymous
 */
function setBlockUndefinedIP()
{
    try {
        PolicyAjax.callAsync('IPBlockingBlockUndefined', $('#block_undefined_ip').prop('checked'));
    } catch(e) {
        alert(e);
    }
}

/**
 * setAgentBlockUndefined
 */
function setBlockUndefinedAgent()
{
    try {
        PolicyAjax.callAsync(
            'AgentBlockingBlockUndefined',
            $('#block_undefined_agent').prop('checked')
        );
    } catch(e) {
        alert(e);
    }
}

/**
 * save encryption settings
 */
function saveEncryptionSettings()
{
    try {
        PolicyAjax.callAsync(
            'UpdateEncryptionSettings', [
                $('#enabled').val(),
                $('#key_age').val(),
                $('#key_len').val()
            ]
        );
    } catch(e) {
        alert(e);
    }
}

/**
 * save AntiSpam settings
 */
function saveAntiSpamSettings()
{
    try {
        PolicyAjax.callAsync(
            'UpdateAntiSpamSettings', [
                $('#filter').val(),
                $('#default_captcha').val(),
                $('#default_captcha_driver').val(),
                $('#obfuscator').val(),
                $('#blocked_domains').val()
            ]
        );
    } catch(e) {
        alert(e);
    }
}

/**
 * save Advanced Policies
 */
function saveAdvancedPolicies()
{
    try {
        PolicyAjax.callAsync(
            'UpdateAdvancedPolicies', [
                $('#password_complexity').val(),
                $('#password_bad_count').val(),
                $('#password_lockedout_time').val(),
                $('#password_max_age').val(),
                $('#password_min_length').val(),
                $('#login_captcha').val(),
                $('#login_captcha_driver').val(),
                $('#xss_parsing_level').val(),
                $('#session_idle_timeout').val(),
                $('#session_remember_timeout').val()
            ]
        );
    } catch(e) {
        alert(e);
    }
}

/**
 * Submit the form
 */
function submitForm(form)
{
    switch (form.elements['action'].value) {
        case 'AddIPBand':
            addIPBand(form);
            break;
        case 'AddAgent':
            addAgent(form);
            break;
        case 'UpdateProperties':
            updateProperties(form);
            break;
        default:
            break;
    }
}

/**
 * Clean the form
 */
function stopAction()
{
    switch (currentAction) {
        case 'IPBlocking':
            $('#id').val(0);
            $('#from_ipaddress').val('');
            $('#to_ipaddress').val('');
            $('#script').val('index');
            unselectDataGridRow();
            break;
        case 'AgentBlocking':
            $('#id').val(0);
            $('#agent').val('');
            $('#script').val('index');
            unselectDataGridRow();
            break;
        default:
            break;
    }
}

$(document).ready(function() {
    switch (jaws.Defines.mainAction) {
        case 'IPBlocking':
            currentAction = 'IPBlocking';
            initDataGrid('blocked_ips_datagrid', PolicyAjax);
            break;

        case 'AgentBlocking':
            currentAction = 'AgentBlocking';
            initDataGrid('blocked_agents_datagrid', PolicyAjax);
            break;

        case 'Encryption':
            currentAction = 'Encryption';
            break;

        case 'AntiSpam':
            currentAction = 'AntiSpam';
            break;

        case 'AdvancedPolicies':
            currentAction = 'AdvancedPolicies';
            break;
    }
});

var PolicyAjax = new JawsAjax('Policy', PolicyCallback);

//Which action are we running?
var currentAction = null;

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
