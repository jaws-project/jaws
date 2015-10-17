/**
 * Policy Javascript actions
 *
 * @category   Ajax
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var PolicyCallback = {
    AddIPRange: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('blocked_ips_datagrid')[0].addItem();
            $('blocked_ips_datagrid')[0].setCurrentPage(0);
            getDG();
            stopAction();
        }
        PolicyAjax.showResponse(response);
    },

    EditIPRange: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            getDG();
        }
        PolicyAjax.showResponse(response);
    },

    DeleteIPRange: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('blocked_ips_datagrid')[0].deleteItem();
            getDG();
        }
        PolicyAjax.showResponse(response);
    },

    AddAgent: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('blocked_agents_datagrid')[0].addItem();
            $('blocked_agents_datagrid')[0].setCurrentPage(0);
            getDG();
            stopAction();
        }
        PolicyAjax.showResponse(response);
    },

    EditAgent: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            getDG();
        }
        PolicyAjax.showResponse(response);
    },

    DeleteAgent: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('blocked_agents_datagrid')[0].deleteItem();
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
}

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
    if ($(field + '_captcha').value == 'DISABLED') {
        $(field + '_captcha_driver').disabled = true;
    } else {
        $(field + '_captcha_driver').disabled = false;
    }
}

/**
 * Add/Edit Blocked a IP Range
 */
function saveIPRange()
{
    if (!$('from_ipaddress').val()) {
        alert(incompleteFields);
        return false;
    }

    if ($('id').value == 0) {
        PolicyAjax.callAsync(
            'AddIPRange', [
                $('#from_ipaddress').val(),
                $('#to_ipaddress').val(),
                $('#blocked').val()
            ]
        );
    } else {
        PolicyAjax.callAsync(
            'EditIPRange', [
                $('#id').val(),
                $('#from_ipaddress').val(),
                $('#to_ipaddress').val(),
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
    selectDataGridRow(element.parentNode.parentNode);
    var ipRange = PolicyAjax.callSync('GetIPRange', id);

    $('id').value = ipRange['id'];
    $('from_ipaddress').value = ipRange['from_ip'];
    $('to_ipaddress').value   = ipRange['to_ip'];
    $('blocked').selectedIndex = ipRange['blocked']? 1 : 0;
}

/**
 * Delete an IP range
 */
function deleteIPRange(element, id)
{
    stopAction();
    selectDataGridRow(element.parentNode.parentNode);
    var answer = confirm(confirmIPRangeDelete);
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
    if (!$('agent').val()) {
        alert(incompleteFields);
        return false;
    }

    if ($('id').value == 0) {
        PolicyAjax.callAsync('AddAgent', [$('#agent').val(), $('#blocked').val()]);
    } else {
        PolicyAjax.callAsync(
            'EditAgent',
            [$('#id').val(), $('#agent').val(), $('#blocked').val()]
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
    selectDataGridRow(element.parentNode.parentNode);
    var agent = PolicyAjax.callSync('GetAgent', id);

    $('id').value    = agent['id'];
    $('agent').value = agent['agent'].defilter();
    $('blocked').selectedIndex = agent['blocked']? 1 : 0;
}

/**
 * Delete an Agent
 */
function deleteAgent(element, id)
{
    stopAction();
    selectDataGridRow(element.parentNode.parentNode);
    var answer = confirm(confirmAgentDelete);
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
        PolicyAjax.callAsync('IPBlockingBlockUndefined', $('block_undefined_ip').checked);
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
            $('block_undefined_agent').checked
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
            $('id').value = 0;
            $('from_ipaddress').value = '';
            $('to_ipaddress').value   = '';
            unselectDataGridRow();
            break;
        case 'AgentBlocking':
            $('id').value = 0;
            $('agent').value = '';
            unselectDataGridRow();
            break;
        default:
            break;
    }
}

var PolicyAjax = new JawsAjax('Policy', PolicyCallback);

//Which action are we runing?
var currentAction = null;

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
