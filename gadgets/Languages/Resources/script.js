/**
 * Languages Javascript actions
 *
 * @category   Ajax
 * @package    Languages
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
function Jaws_Gadget_Languages() { return {
    // ASync callback method
    AjaxCallback : {
    },
}};
/**
 * Use async mode, create Callback
 */
var LanguagesCallback = {
    SaveLanguage: function(response) {
        if (response['type'] == 'alert-success') {
            setTimeout( "refresh()", 1000);
        }
    },

    SetLangData: function(response) {
        if (response['type'] == 'alert-success') {
            changeColorOfTranslatedTerms();
        }
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
        LanguagesAjax.call('SaveLanguage', lang_str);
    }
}

/**
 *
 */
function changeColorOfTranslatedTerms()
{
    var strings_elements = $('#tbl_strings textarea');
    for(var i = 0; i < strings_elements.length; i++) {
        if (jQuery(strings_elements[i]).val() != "") {
            jQuery(strings_elements[i]).parent().parent().find('span').eq(0).css('color', "#000");
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
            if ($('#checkbox_filter').prop('checked') && jQuery(strings_elements[i]).val() != "") {
                jQuery(strings_elements[i]).parent().parent().css('display', 'none');
            } else {
                jQuery(strings_elements[i]).parent().parent().css('display', 'inline');
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
    $('#btn_lang').append(imgBtn);
    $('#btn_lang').append(text);
}

/**
 *
 */
function change_lang_option()
{
    if (LangDataChanged) {
        var answer = confirm(Jaws.gadgets.Languages.defines.confirmSaveData);
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
            setButtonTitle(Jaws.gadgets.Languages.defines.add_language_title);
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
        $('#lang_code').val($('#lang :selected').val());
        $('#lang_name').val($('#lang :selected').text());
        if ($('#btn_lang')) {
            setButtonTitle(Jaws.gadgets.Languages.defines.save_language_title);
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
            LanguagesAjax.call('GetLangDataUI', [$('#component').val(), $('#lang').val()], false, {'async': false})
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

    var data = {};
    data['meta'] = {};
    $('#meta_lang input').each(function(index) {
        data['meta'][this.name] = $(this).val();
    });

    data['strings'] = {};
    $('#tbl_strings textarea').each(function(index) {
        data['strings'][this.name] = $(this).val();
    });

    LanguagesAjax.call('SetLangData', [component, lang, data]);
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
    window.location= LanguagesAjax.baseScript + '?reqGadget=Languages&reqAction=Export&lang=' + $('#lang').val();
}

$(document).ready(function() {
    switch (Jaws.defines.mainAction) {
        case 'Languages':
            change_lang_option();
            $('#component').selectedIndex = -1;
            break;

    }
});

var LanguagesAjax = new JawsAjax('Languages', LanguagesCallback);

//data language changed?
var LangDataChanged = false

//Which language are selected?
var lang = '';

//Which component are selected?
var component = '';

//New language string
var lang_str = '';
