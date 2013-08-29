/**
 * Languages Javascript actions
 *
 * @category   Ajax
 * @package    Languages
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var LanguagesCallback = {
    savelanguage: function(response) {
        if (response[0]['css'] == 'notice-message') {
            setTimeout( "refresh()", 1000);
        }
        showResponse(response);
    },

    setlangdata: function(response) {
        if (response[0]['css'] == 'notice-message') {
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
    if (!_('lang_code').value.blank() &&
        !_('lang_name').value.blank())
    {
        lang_str = _('lang_code').value.trim() + ';' + _('lang_name').value.trim();
        LanguagesAjax.callAsync('savelanguage', lang_str);
    }
}

/**
 *
 */
function changeColorOfTranslatedTerms()
{
    var strings_elements = _('tbl_strings').getElementsByTagName('textarea');
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
    if (_('tbl_strings')) {
        var strings_elements = _('tbl_strings').getElementsByTagName('textarea');
        for(var i = 0; i < strings_elements.length; i++) {
            if (_('checkbox_filter').checked && strings_elements[i].value != "") {
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
    imgBtn = _('btn_lang').getElementsByTagName('img')[0];
    text = document.createTextNode(' ' + title);
    _('btn_lang').innerHTML = '';
    _('btn_lang').appendChild(imgBtn);
    _('btn_lang').appendChild(text);
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

    if (_('lang').selectedIndex == 0) {
        _('btn_export').disabled = true;
        _('lang_code').disabled  = false;
        _('component').disabled  = true;
        _('lang_code').value = '';
        _('lang_name').value = '';
        if (_('btn_lang')) {
            setButtonTitle(add_language_title);
        } else {
            _('lang_name').disabled  = true;
        }
        _('lang_code').focus();
        stopAction();
        return;
    } else {
        _('btn_export').disabled = false;
        _('lang_code').disabled  = true;
        _('component').disabled  = false;
        _('lang_code').value = _('lang').options[_('lang').selectedIndex].value;
        _('lang_name').value = _('lang').options[_('lang').selectedIndex].text;
        if (_('btn_lang')) {
            setButtonTitle(save_language_title);
        } else {
            _('lang_name').disabled  = true;
        }
    }

    lang = _('lang').value;
    component = _('component').value;

    if (!_('lang').value.blank() && 
        !_('component').value.blank())
    {
        _('btn_save').style.visibility = 'visible';
        _('btn_cancel').style.visibility = 'visible';
        _('lang_strings').innerHTML = LanguagesAjax.callSync('getlangdataui', _('component').value, _('lang').value);
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
    var meta_elements = _('meta_lang').getElementsByTagName('input');
    data['meta'] = new Array();
    for(var i = 0; i < meta_elements.length; i++) {
        data['meta'][meta_elements[i].name] = meta_elements[i].value;
    }

    var strings_elements = _('tbl_strings').getElementsByTagName('textarea');
    data['strings'] = new Array();
    for(var i = 0; i < strings_elements.length; i++) {
        data['strings'][strings_elements[i].name] = strings_elements[i].value;
    }

    LanguagesAjax.callAsync('setlangdata', component, lang, data);
    LangDataChanged = false;
    data = null;
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    _('btn_save').style.visibility   = 'hidden';
    _('btn_cancel').style.visibility = 'hidden';
    _('component').selectedIndex = -1;
    _('lang_strings').innerHTML = '';
    LangDataChanged = false;
}

/**
 * Export language
 */
function export_lang()
{
    window.location= base_script + '?gadget=Languages&action=Export&lang=' + _('lang').value;
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
