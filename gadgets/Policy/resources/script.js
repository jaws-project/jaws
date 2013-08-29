/**
 * Policy Javascript actions
 *
 * @category   Ajax
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var PolicyCallback = {
    addiprange: function(response) {
        if (response[0]['css'] == 'notice-message') {
            _('blocked_ips_datagrid').addItem();
            _('blocked_ips_datagrid').setCurrentPage(0);
            getDG();
            stopAction();
        }
        showResponse(response);
    },

    editiprange: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG();
        }
        showResponse(response);
    },

    deleteiprange: function(response) {
        if (response[0]['css'] == 'notice-message') {
            _('blocked_ips_datagrid').deleteItem();
            getDG();
        }
        showResponse(response);
    },

    addagent: function(response) {
        if (response[0]['css'] == 'notice-message') {
            _('blocked_agents_datagrid').addItem();
            _('blocked_agents_datagrid').setCurrentPage(0);
            getDG();
            stopAction();
        }
        showResponse(response);
    },

    editagent: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG();
        }
        showResponse(response);
    },

    deleteagent: function(response) {
        if (response[0]['css'] == 'notice-message') {
            _('blocked_agents_datagrid').deleteItem();
            getDG();
        }
        showResponse(response);
    },

    ipblockingblockundefined: function(response) {
        showResponse(response);
    },

    agentblockingblockundefined: function(response) {
        showResponse(response);
    },

    updateencryptionsettings: function(response) {
        showResponse(response);
    },

    updateantispamsettings: function(response) {
        showResponse(response);
    },

    updateadvancedpolicies: function(response) {
        showResponse(response);
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
    if (_(field + '_captcha').value == 'DISABLED') {
        _(field + '_captcha_driver').disabled = true;
    } else {
        _(field + '_captcha_driver').disabled = false;
    }
}

/**
 * Add/Edit Blocked a IP Range
 */
function saveIPRange()
{
    if (_('from_ipaddress').value.blank()) {
        alert(incompleteFields);
        return false;
    }

    if (_('id').value == 0) {
        PolicyAjax.callAsync(
            'addiprange',
            _('from_ipaddress').value,
            _('to_ipaddress').value,
            _('blocked').value
        );
    } else {
        PolicyAjax.callAsync(
            'editiprange',
            _('id').value,
            _('from_ipaddress').value,
            _('to_ipaddress').value,
            _('blocked').value
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
    var ipRange = PolicyAjax.callSync('getiprange', id);

    _('id').value = ipRange['id'];
    _('from_ipaddress').value = ipRange['from_ip'];
    _('to_ipaddress').value   = ipRange['to_ip'];
    _('blocked').selectedIndex = ipRange['blocked']? 1 : 0;
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
        PolicyAjax.callAsync('deleteiprange', id);
    }
    unselectDataGridRow();
}

/**
 * Add/Edit Blocked Agent
 */
function saveAgent()
{
    if (_('agent').value.blank()) {
        alert(incompleteFields);
        return false;
    }

    if (_('id').value == 0) {
        PolicyAjax.callAsync('addagent', _('agent').value, _('blocked').value);
    } else {
        PolicyAjax.callAsync('editagent', _('id').value, _('agent').value, _('blocked').value);
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
    var agent = PolicyAjax.callSync('getagent', id);

    _('id').value    = agent['id'];
    _('agent').value = agent['agent'].defilter();
    _('blocked').selectedIndex = agent['blocked']? 1 : 0;
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
        PolicyAjax.callAsync('deleteagent', id);
    }
    unselectDataGridRow();
}

/**
 * setIPBlockAnonymous
 */
function setBlockUndefinedIP()
{
    try {
        PolicyAjax.callAsync('ipblockingblockundefined', _('block_undefined_ip').checked);
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
            'agentblockingblockundefined',
            _('block_undefined_agent').checked
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
            'updateencryptionsettings',
            _('enabled').value,
            _('key_age').value,
            _('key_len').value
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
            'updateantispamsettings',
            _('filter').value,
            _('default_captcha').value,
            _('default_captcha_driver').value,
            _('obfuscator').value
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
            'updateadvancedpolicies',
            _('passwd_complexity').value,
            _('passwd_bad_count').value,
            _('passwd_lockedout_time').value,
            _('passwd_max_age').value,
            _('passwd_min_length').value,
            _('login_captcha').value,
            _('login_captcha_driver').value,
            _('xss_parsing_level').value,
            _('session_idle_timeout').value,
            _('session_remember_timeout').value
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
            _('id').value = 0;
            _('from_ipaddress').value = '';
            _('to_ipaddress').value   = '';
            unselectDataGridRow();
            break;
        case 'AgentBlocking':
            _('id').value = 0;
            _('agent').value = '';
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
