/**
 * Languages Javascript actions
 *
 * @category   Ajax
 * @package    Languages
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var LanguagesCallback = {
    SaveLanguage: function(response) {
        if (response[0]['type'] == 'response_notice') {
            setTimeout( "refresh()", 1000);
        }
        LanguagesAjax.showResponse(response);
    },

    SetLangData: function(response) {
        if (response[0]['type'] == 'response_notice') {
            changeColorOfTranslatedTerms();
        }
        LanguagesAjax.showResponse(response);
    }
}

/**
 * refresh page
 */
function refresh()
{
    document.location.reload();
}

/**
 * Add new language
 */
function save_lang()
{
    if ($('#lang_code').val() &&
        $('#lang_name').val())
    {
        lang_str = $('#lang_code').val().trim() + ';' + $('#lang_name').val().trim();
        LanguagesAjax.callAsync('SaveLanguage', lang_str);
    }
}

/**
 *
 */
function changeColorOfTranslatedTerms()
{
    var strings_elements = $('#tbl_strings textarea');
    for(var i = 0; i < strings_elements.length; i++) {
        if (strings_elements[i].value != "") {
            strings_elements[i].parentNode.parentNode.getElementsByTagName('span')[0].style.color="#000";
        }
    }
}

/**
 *
 */
function filterTranslated()
{
    if ($('#tbl_strings')) {
        var strings_elements = $('#tbl_strings textarea');
        for(var i = 0; i < strings_elements.length; i++) {
            if ($('#checkbox_filter').checked && strings_elements[i].value != "") {
                strings_elements[i].parentNode.parentNode.style.display = 'none';
            } else {
                strings_elements[i].parentNode.parentNode.style.display = 'inline';
            }
        }
    }
}

/**
 *
 */
function setButtonTitle(title)
{
    imgBtn = $('#btn_lang img').first();
    text = document.createTextNode(' ' + title);
    $('#btn_lang').html('');
    $('#btn_lang').appendChild(imgBtn);
    $('#btn_lang').appendChild(text);
}

/**
 *
 */
function change_lang_option()
{
    if (LangDataChanged) {
        var answer = confirm(confirmSaveData);
        if (answer) {
            save_lang_data();
        }
        LangDataChanged = false;
    }

    if ($('#lang').prop('selectedIndex') == 0) {
        $('#btn_export').prop('disabled', true);
        $('#lang_code').prop('disabled', false);
        $('#component').prop('disabled', true);
        $('#lang_code').val('');
        $('#lang_name').val('');
        if ($('#btn_lang')) {
            setButtonTitle(add_language_title);
        } else {
            $('#lang_name').prop('disabled', true);
        }
        $('#lang_code').focus();
        stopAction();
        return;
    } else {
        $('#btn_export').prop('disabled', false);
        $('#lang_code').prop('disabled', true);
        $('#component').prop('disabled', false);
        $('#lang_code').val($('#lang').options[$('#lang').prop('selectedIndex')].value);
        $('#lang_name').val($('#lang').options[$('#lang').prop('selectedIndex')].text);
        if ($('#btn_lang')) {
            setButtonTitle(save_language_title);
        } else {
            $('#lang_name').prop('disabled', true);
        }
    }

    lang = $('#lang').val();
    component = $('#component').val();

    if ($('#lang').val() && 
        $('#component').val())
    {
        $('#btn_save').css('visibility', 'visible');
        $('#btn_cancel').css('visibility', 'visible');
        $('#lang_strings').html(
            LanguagesAjax.callSync('GetLangDataUI', [$('#component').val(), $('#lang').val()])
        );
        filterTranslated();
    }
}

/**
 *
 */
function save_lang_data()
{
    if (lang.blank() || component.blank()) {
        // display message there
        return;
    }

    var data = new Array();
    var meta_elements = $('#meta_lang input');
    data['meta'] = new Array();
    for(var i = 0; i < meta_elements.length; i++) {
        data['meta'][meta_elements[i].name] = meta_elements[i].value;
    }

    var strings_elements = $('#tbl_strings textarea');
    data['strings'] = new Array();
    for(var i = 0; i < strings_elements.length; i++) {
        data['strings'][strings_elements[i].name] = strings_elements[i].value;
    }

    LanguagesAjax.callAsync('SetLangData', [component, lang, data]);
    LangDataChanged = false;
    data = null;
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    $('#btn_save').css('visibility', 'hidden');
    $('#btn_cancel').css('visibility', 'hidden');
    $('#component').prop('selectedIndex', -1);
    $('#lang_strings').html('');
    LangDataChanged = false;
}

/**
 * Export language
 */
function export_lang()
{
    window.location= LanguagesAjax.baseScript + '?gadget=Languages&action=Export&lang=' + $('#lang').val();
}

var LanguagesAjax = new JawsAjax('Languages', LanguagesCallback);

//data language changed?
var LangDataChanged = false

//Which language are selected?
var lang = '';

//Which component are selected?
var component = '';

//New language string
var lang_str = '';
