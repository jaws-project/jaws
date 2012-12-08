/**
 * Preferences Javascript actions
 *
 * @category   Ajax
 * @package    Preferences
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var PreferencesCallback = {
    updatepreferences: function(response) {
        showResponse(response);
    }
}

/**
 * Update preferences
 */
function updatePreferences()
{
    var preferences_config = [];
    preferences_config['display_theme']             = document.getElementsByName('display[]')[0].checked;
    preferences_config['display_editor']            = document.getElementsByName('display[]')[1].checked;
    preferences_config['display_language']          = document.getElementsByName('display[]')[2].checked;
    preferences_config['display_calendar_type']     = document.getElementsByName('display[]')[3].checked;
    preferences_config['display_calendar_language'] = document.getElementsByName('display[]')[4].checked;
    preferences_config['display_date_format']       = document.getElementsByName('display[]')[5].checked;
    preferences_config['display_timezone']          = document.getElementsByName('display[]')[6].checked;
    preferences_config['cookie_precedence']         = document.getElementsByName('display[]')[7].checked;

    PreferencesAjax.callAsync('updatepreferences', preferences_config);
}


var PreferencesAjax = new JawsAjax('Preferences', PreferencesCallback);