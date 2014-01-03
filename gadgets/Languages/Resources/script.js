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
        showResponse(response);
    },

    SetLangData: function(response) {
        if (response[0]['type'] == 'response_notice') {
            changeColorOfTranslatedTerms();
        }
        showResponse(response);
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
    if (!$('lang_code').value.blank() &&
        !$('lang_name').value.blank())
    {
        lang_str = $('lang_code').value.trim() + ';' + $('lang_name').value.trim();
        LanguagesAjax.callAsync('SaveLanguage', lang_str);
    }
}

/**
 *
 */
function changeColorOfTranslatedTerms()
{
    var strings_elements = $('tbl_strings').getElementsByTagName('textarea');
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
    if ($('tbl_strings')) {
        var strings_elements = $('tbl_strings').getElementsByTagName('textarea');
        for(var i = 0; i < strings_elements.length; i++) {
            if ($('checkbox_filter').checked && strings_elements[i].value != "") {
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
    imgBtn = $('btn_lang').getElementsByTagName('img')[0];
    text = document.createTextNode(' ' + title);
    $('btn_lang').innerHTML = '';
    $('btn_lang').appendChild(imgBtn);
    $('btn_lang').appendChild(text);
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

    if ($('lang').selectedIndex == 0) {
        $('btn_export').disabled = true;
        $('lang_code').disabled  = false;
        $('component').disabled  = true;
        $('lang_code').value = '';
        $('lang_name').value = '';
        if ($('btn_lang')) {
            setButtonTitle(add_language_title);
        } else {
            $('lang_name').disabled  = true;
        }
        $('lang_code').focus();
        stopAction();
        return;
    } else {
        $('btn_export').disabled = false;
        $('lang_code').disabled  = true;
        $('component').disabled  = false;
        $('lang_code').value = $('lang').options[$('lang').selectedIndex].value;
        $('lang_name').value = $('lang').options[$('lang').selectedIndex].text;
        if ($('btn_lang')) {
            setButtonTitle(save_language_title);
        } else {
            $('lang_name').disabled  = true;
        }
    }

    lang = $('lang').value;
    component = $('component').value;

    if (!$('lang').value.blank() && 
        !$('component').value.blank())
    {
        $('btn_save').style.visibility = 'visible';
        $('btn_cancel').style.visibility = 'visible';
        $('lang_strings').innerHTML = LanguagesAjax.callSync('GetLangDataUI', $('component').value, $('lang').value);
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
    var meta_elements = $('meta_lang').getElementsByTagName('input');
    data['meta'] = new Array();
    for(var i = 0; i < meta_elements.length; i++) {
        data['meta'][meta_elements[i].name] = meta_elements[i].value;
    }

    var strings_elements = $('tbl_strings').getElementsByTagName('textarea');
    data['strings'] = new Array();
    for(var i = 0; i < strings_elements.length; i++) {
        data['strings'][strings_elements[i].name] = strings_elements[i].value;
    }

    LanguagesAjax.callAsync('SetLangData', component, lang, data);
    LangDataChanged = false;
    data = null;
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    $('btn_save').style.visibility   = 'hidden';
    $('btn_cancel').style.visibility = 'hidden';
    $('component').selectedIndex = -1;
    $('lang_strings').innerHTML = '';
    LangDataChanged = false;
}

/**
 * Export language
 */
function export_lang()
{
    window.location= LanguagesAjax.baseScript + '?gadget=Languages&action=Export&lang=' + $('lang').value;
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
