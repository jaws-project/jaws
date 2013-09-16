/**
 * PrivateMessage Javascript actions
 *
 * @category    Ajax
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
var PrivateMessageCallback = {
    savedraftmessage: function(response) {
        showResponse(response);
    }
}
/**
 * get users list with custom term
 */
function getUsers(term) {
    return pmAjax.callSync('GetUsers', term);
}

/**
 * get groups list with custom term
 */
function getGroups(term) {
   return pmAjax.callSync('GetGroups', term);
}

/**
 * get groups list with custom term
 */
function saveDraft(id) {
    var data = new Array();
    data['id'] = id;
    data['parent'] = $('parent').value;
    data['recipient_users'] = $('recipient_users').value;
    data['recipient_groups'] = $('recipient_groups').value;
    data['subject'] = $('subject').value;
    data['body'] = $('body').value;
    data['published'] = false;
//    data['selected_files'] = $('body').value;
   pmAjax.callAsync('SaveDraftMessage', data);
}

var pmAjax = new JawsAjax('PrivateMessage', PrivateMessageCallback, 'index.php');