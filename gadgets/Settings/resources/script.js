/**
 * Settings Javascript actions
 *
 * @category   Ajax
 * @package    Settings
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var SettingsCallback = {
    UpdateBasicSettings: function(response) {
        showResponse(response);
    },

    UpdateAdvancedSettings: function(response) {
        showResponse(response);
    },

    UpdateMetaSettings: function(response) {
        showResponse(response);
    },

    UpdateMailSettings: function(response) {
        showResponse(response);
    },

    UpdateFTPSettings: function(response) {
        showResponse(response);
    },

    UpdateProxySettings: function(response) {
        showResponse(response);
    }
}

/**
 * Update basic settings
 */
function submitBasicForm()
{
    SettingsAjax.callAsync('UpdateBasicSettings', $('#settingsForm :input').serializeArray());
}

/**
 * Update advanced settings
 */
function submitAdvancedForm()
{
    SettingsAjax.callAsync('UpdateAdvancedSettings', $('#settingsForm :input').serializeArray());
}

/**
 * Adds new custom meta
 */
function addCustomMeta()
{
    var div = $('<div>', {'class': 'fields'}),
        label = $('<label>').html(custom_meta),
        inputName  = $('<input>', {type:'text', title:'Meta Name', 'class':'meta-name'}),
        inputValue = $('<input>', {type:'text', title:'Meta Content', 'class':'meta-value'});

    div.append(label);
    div.append(inputName);
    div.append(inputValue);
    $('#customMeta').append(div);
}

/**
 * Update meta
 */
function submitMetaForm()
{
    var customMeta = [];
    var customInputs = $('#customMeta input.meta-name');
    customInputs.each(function(index, input) {
        if (!$(input).val()) {
            $(input).parent().empty();
            return;
        }
        customMeta[index] = [$(input).val(), $(input).next().val()];
    });

    SettingsAjax.callAsync(
        'UpdateMetaSettings',
        $('#settingsForm :input').serializeArray(),
        customMeta
    );
}

/**
 * Update mailserver settings
 */
function submitMailSettingsForm()
{
    SettingsAjax.callAsync('UpdateMailSettings', $('#settingsForm :input').serializeArray());
}

/**
 * Update ftpserver settings
 */
function submitFTPSettingsForm()
{
    SettingsAjax.callAsync('UpdateFTPSettings', $('#settingsForm :input').serializeArray());
}

/**
 * Update proxy settings
 */
function submitProxySettingsForm()
{
    SettingsAjax.callAsync('UpdateProxySettings', $('#settingsForm :input').serializeArray());
}

function toggleGR() 
{
    if ($('use_gravatar').value == 'yes') {
        $('gravatar_rating').disabled = false;
    } else {
        $('gravatar_rating').disabled = true;
    }
}

function changeMailer()
{
    switch($('mailer').value) {
    case 'DISABLED':
        $('gate_email').disabled = true;
        $('gate_title').disabled = true;
        $('sendmail_path').disabled = true;
        $('smtp_host').disabled  = true;
        $('smtp_port').disabled  = true;
        $('smtp_auth').disabled  = true;
        $('smtp_user').disabled  = true;
        $('smtp_pass').disabled  = true;
        break;
    case 'phpmail':
        $('gate_email').disabled = false;
        $('gate_title').disabled = false;
        $('sendmail_path').disabled = true;
        $('smtp_host').disabled  = true;
        $('smtp_port').disabled  = true;
        $('smtp_auth').disabled  = true;
        $('smtp_user').disabled  = true;
        $('smtp_pass').disabled  = true;
        break;
    case 'sendmail':
        $('gate_email').disabled = false;
        $('gate_title').disabled = false;
        $('sendmail_path').disabled = false;
        $('smtp_host').disabled  = true;
        $('smtp_port').disabled  = true;
        $('smtp_auth').disabled  = true;
        $('smtp_user').disabled  = true;
        $('smtp_pass').disabled  = true;
        break;
    case 'smtp':
        $('gate_email').disabled = false;
        $('gate_title').disabled = false;
        $('sendmail_path').disabled = true;
        $('smtp_host').disabled  = false;
        $('smtp_port').disabled  = false;
        $('smtp_auth').disabled  = false;
        $('smtp_user').disabled  = false;
        $('smtp_pass').disabled  = false;
        break;
    }
}

var SettingsAjax = new JawsAjax('Settings', SettingsCallback);
