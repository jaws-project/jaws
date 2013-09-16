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
    data[1] = '';

   return pmAjax.callASync('SaveDraftMessage', id, data);
}

var pmAjax = new JawsAjax('PrivateMessage', PrivateMessageCallback, 'index.php');