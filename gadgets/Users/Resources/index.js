/**
 * Users Javascript front-end actions
 *
 * @category    Ajax
 * @package     Users
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var UsersCallback = {
    UpdateContacts: function(response) {
        UsersAjax.showResponse(response);
    },

    UpdatePreferences: function(response) {
        UsersAjax.showResponse(response);
    }

}

/**
 * Update contacts
 */
function updateContacts()
{
    var result = UsersAjax.callAsync(
        'UpdateContacts',
        $.unserialize($('form[name=contacts]').serialize())
    );
    return false;
}

/**
 * Update preferences
 */
function updatePreferences(form)
{
    var result = UsersAjax.callAsync(
        'UpdatePreferences',
        $.unserialize($(form).serialize())
    );
    return false;
}

var UsersAjax = new JawsAjax('Users', UsersCallback);
