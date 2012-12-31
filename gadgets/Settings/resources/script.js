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
    var form = $('settingsForm'),
        settingsArray = new Array();
    for (i=0; i<form.elements.length; i++) {
        settingsArray[form.elements[i].name] = form.elements[i].value;
    }
    SettingsAjax.callAsync('UpdateBasicSettings', settingsArray);
}

/**
 * Update advanced settings
 */
function submitAdvancedForm()
{
    var form = $('settingsForm'),
        settingsArray = new Array();
    for (i=0; i<form.elements.length; i++) {
        settingsArray[form.elements[i].name] = form.elements[i].value;
    }
    SettingsAjax.callAsync('UpdateAdvancedSettings', settingsArray);
}

/**
 * Adds new custom meta
 */
function addCustomMeta()
{
    var div = new Element('div', {'class':'fields'}),
        label = new Element('label').set('html', custom_meta),
        inputName  = new Element('input', {type:'text', title:'Meta Name', 'class':'meta-name'}),
        inputValue = new Element('input', {type:'text', title:'Meta Content', 'class':'meta-value'});

    div.adopt(label);
    div.adopt(inputName);
    div.adopt(inputValue);
    $('customMeta').adopt(div);
}

/**
 * Update meta
 */
function submitMetaForm()
{
    var form = $('settingsForm'),
        settingsArray = new Array();

    for (i=0; i<form.elements.length; i++) {
        settingsArray[form.elements[i].name] = form.elements[i].value;
    }

    var customMeta   = new Array(),
        customInputs = $('customMeta').getElements('input.meta-name');
    customInputs.each(function(input) {
        if (input.value.blank()) {
            input.getParent().destroy();
            return;
        }
        customMeta.include([input.value, input.getNext().value]);
    });
    SettingsAjax.callAsync('UpdateMetaSettings', settingsArray, customMeta);
}

/**
 * Update mailserver settings
 */
function submitMailSettingsForm()
{
    var form = $('settingsForm'),
        settingsArray = new Array();
    for (i=0; i<form.elements.length; i++) {
        settingsArray[form.elements[i].name] = form.elements[i].value;
    }
    SettingsAjax.callAsync('UpdateMailSettings', settingsArray);
}

/**
 * Update ftpserver settings
 */
function submitFTPSettingsForm()
{
    var form = $('settingsForm'),
        settingsArray = new Array();
    for (i=0; i<form.elements.length; i++) {
        settingsArray[form.elements[i].name] = form.elements[i].value;
    }
    SettingsAjax.callAsync('UpdateFTPSettings', settingsArray);
}

/**
 * Update proxy settings
 */
function submitProxySettingsForm()
{
    var form = $('settingsForm'),
        settingsArray = new Array();
    for (i=0; i<form.elements.length; i++) {
        settingsArray[form.elements[i].name] = form.elements[i].value;
    }
    SettingsAjax.callAsync('UpdateProxySettings', settingsArray);
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
