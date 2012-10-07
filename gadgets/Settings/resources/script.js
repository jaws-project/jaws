/**
 * Settings Javascript actions
 *
 * @category   Ajax
 * @package    Settings
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var SettingsCallback = {
    updatebasicsettings: function(response) {
        showResponse(response);
    },

    updateadvancedsettings: function(response) {
        showResponse(response);
    },

    updatemetasettings: function(response) {
        showResponse(response);
    },

    updatemailsettings: function(response) {
        showResponse(response);
    },

    updateftpsettings: function(response) {
        showResponse(response);
    },

    updateproxysettings: function(response) {
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
    settings.updatebasicsettings(settingsArray);
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
    settings.updateadvancedsettings(settingsArray);
}

/**
 * Adds new custom meta
 */
function addCustomMeta()
{
    var div = new Element('div', {'class':'fields'}),
        label = new Element('label').update(custom_meta),
        inputName  = new Element('input', {type:'text', title:'Meta Name', 'class':'meta-name'}),
        inputValue = new Element('input', {type:'text', title:'Meta Content', 'class':'meta-value'});

    div.insert(label);
    div.insert(inputName);
    div.insert(inputValue);
    $('customMeta').insert(div);
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
        customInputs = $('customMeta').select('input.meta-name');
    customInputs.each(function(input) {
        if (input.value.blank()) {
            input.up().remove();
            return;
        }
        customMeta.push([input.value, input.next().value]);
    });

    settings.updatemetasettings(settingsArray, customMeta);
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
    settings.updatemailsettings(settingsArray);
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
    settings.updateftpsettings(settingsArray);
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
    settings.updateproxysettings(settingsArray);
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

var settings = new settingsadminajax(SettingsCallback);
settings.serverErrorFunc = Jaws_Ajax_ServerError;
settings.onInit = showWorkingNotification;
settings.onComplete = hideWorkingNotification;
