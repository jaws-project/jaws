/**
 * Comments Javascript actions
 *
 * @category   Ajax
 * @package    Comments
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var CommentsCallback = {
 
}

/**
 * Get data
 */
function getData(limit)
{
    if (limit == undefined) {
        limit = $('comments_datagrid').getCurrentPage();
    }
    //var formData = getDataOfLCForm();
    updateCommentsDatagrid(limit, '',
                           '', '',
                           false);
}

/**
 * Update comments datagrid
 */
function updateCommentsDatagrid(limit, filter, search, status, resetCounter)
{
    result = CommentsAjax.callSync('SearchComments', limit, '', '', '');
    resetGrid('comments_datagrid', result);
    if (resetCounter) {
        var size = BlogAjax.callSync('sizeofcommentssearch', '', '', '');
        $('comments_datagrid').rowsSize    = size;
        $('comments_datagrid').setCurrentPage(0);
        $('comments_datagrid').updatePageCounter();
    }
}

function isValidEmail(email) {
    return (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(email));
}

/**
 * Select DataGrid row
 *
 */
function selectDataGridRow(rowElement)
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRowColor = rowElement.style.backgroundColor;
    rowElement.style.backgroundColor = '#ffffcc';
    selectedRow = rowElement;
}

/**
 * Unselect DataGrid row
 *
 */
function unselectDataGridRow()
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRow = null;
    selectedRowColor = null;
}

/**
 * Clean the form
 *
 */
function stopAction() 
{
    $('id').value      = 0;
    $('cmments_ip').set('html', '');
    $('name').value    = '';
    $('email').value   = '';
    $('url').value     = '';
    $('subject').value = '';
    $('message').value = '';
    $('btn_save').style.visibility   = 'hidden';
    $('btn_cancel').style.visibility = 'hidden';
    unselectDataGridRow();
    $('name').focus();
}

/**
 * Edit a Comment
 *
 */
function editComment(element, id)
{
    currentAction = 'Contacts';
    $('legend_title').innerHTML = messageDetail_title;
    if (cacheContactForm != null) {
        $('c_work_area').innerHTML = cacheContactForm;
    }

    selectDataGridRow(element.parentNode.parentNode);

    var contact = ContactAjax.callSync('getcontact', id);
    $('id').value      = contact['id'];
    $('contact_ip').set('html', contact['ip']);
    $('name').value    = contact['name'];
    $('email').value   = contact['email'];
    $('company').value = contact['company'];
    $('url').value     = contact['url'];
    $('tel').value     = contact['tel'];
    $('fax').value     = contact['fax'];
    $('mobile').value  = contact['mobile'];
    $('address').value = contact['address'];
    $('rid').value     = contact['recipient'];
    $('subject').value = contact['subject'].defilter();
    $('message').value = contact['msg_txt'].defilter();
    $('btn_save_send').hide();
    $('btn_save').style.visibility   = 'visible';
    $('btn_cancel').style.visibility = 'visible';

    if (contact['attachment']) {
        $('attachment').href = dataURL + contact['attachment'];
        $('attachment').set('html', contact['attachment']);
        $('tr_attachment').show();
    } else {
        $('tr_attachment').hide();
    }
}

/**
 * Delete contact
 *
 */
function deleteContact(element, id)
{
    stopAction();
    selectDataGridRow(element.parentNode.parentNode);
    if (confirm(confirmContactDelete)) {
        ContactAjax.callAsync('deletecontact', id);
    }
    unselectDataGridRow();
}

var CommentsAjax = new JawsAjax('Comments', CommentsCallback),
    selectedRow = null,
    selectedRowColor = null;
