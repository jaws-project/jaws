/**
 * Settings Javascript actions
 *
 * @category   Ajax
 * @package    Settings
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var SettingsCallback = {
    UpdateBasicSettings: function(response) {
        SettingsAjax.showResponse(response);
    },

    UpdateAdvancedSettings: function(response) {
        SettingsAjax.showResponse(response);
    },

    UpdateMetaSettings: function(response) {
        SettingsAjax.showResponse(response);
    },

    UpdateMailSettings: function(response) {
        SettingsAjax.showResponse(response);
    },

    UpdateFTPSettings: function(response) {
        SettingsAjax.showResponse(response);
    },

    UpdateProxySettings: function(response) {
        SettingsAjax.showResponse(response);
    }
}

/**
 * Update basic settings
 */
function submitBasicForm()
{
    SettingsAjax.callAsync(
        'UpdateBasicSettings',
        $.unserialize($('#settingsForm input,select,textarea').serialize())
    );
}

/**
 * Update advanced settings
 */
function submitAdvancedForm()
{
    SettingsAjax.callAsync(
        'UpdateAdvancedSettings',
        $.unserialize($('#settingsForm input,select,textarea').serialize())
    );
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
    var customMeta   = [],
        customInputs = $('#customMeta input.meta-name');

    customInputs.each(function(index, input) {
        if (!$(input).val()) {
            $(input).parent().empty();
            return;
        }
        customMeta[index] = [$(input).val(), $(input).next().val()];
    });

    var settings = $.unserialize($('#settingsForm input,select,textarea').serialize());
    settings["site_custom_meta"] = customMeta;
    SettingsAjax.callAsync('UpdateMetaSettings', settings);
}

/**
 * Update mail-server settings
 */
function submitMailSettingsForm()
{
    SettingsAjax.callAsync(
        'UpdateMailSettings',
        $.unserialize($('#settingsForm input,select,textarea').serialize())
    );
}

/**
 * Update ftp-server settings
 */
function submitFTPSettingsForm()
{
    SettingsAjax.callAsync(
        'UpdateFTPSettings',
        $.unserialize($('#settingsForm input,select,textarea').serialize())
    );
}

/**
 * Update proxy settings
 */
function submitProxySettingsForm()
{
    SettingsAjax.callAsync(
        'UpdateProxySettings',
        $.unserialize($('#settingsForm input,select,textarea').serialize())
    );
}

function toggleGR() 
{
    if ($('#use_gravatar').val() == 'yes') {
        $('#gravatar_rating').prop('disabled', false);
    } else {
        $('#gravatar_rating').prop('disabled', true);
    }
}

function changeMailer()
{
    $('#settingsForm input,select,textarea').not('#mailer').prop("disabled", true);
    switch($('#mailer').val()) {
        case 'phpmail':
            $('#settingsForm #gate_email,#gate_title').prop("disabled", false);
            break;
        case 'sendmail':
            $('#settingsForm #gate_email,#gate_title,#sendmail_path').prop("disabled", false);
            break;
        case 'smtp':
            $('#settingsForm input,select,textarea').not('#mailer').prop("disabled", false);
            $('#settingsForm #sendmail_path').prop("disabled", true);
            break;
    }
}

var SettingsAjax = new JawsAjax('Settings', SettingsCallback);
