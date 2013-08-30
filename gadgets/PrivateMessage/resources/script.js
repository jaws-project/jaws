/**
 * PrivateMessage Javascript actions
 *
 * @category   Ajax
 * @package    PrivateMessage
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var PrivateMessageCallback = {
    savesettings: function(response) {
        showResponse(response);
    }
}


/**
 * Save settings
 */
function saveSettings()
{
    var method     = _('authtype').value;
    var anon       = _('anon_register').value;
    var repetitive = _('anon_repetitive_email').value;
    var act        = _('anon_activation').value;
    var group      = _('anon_group').value;
    var recover    = _('password_recovery').value;

    UsersAjax.callAsync('savesettings', method, anon, repetitive, act, group, recover);
}

var UsersAjax = new JawsAjax('Users', PrivateMessageCallback);