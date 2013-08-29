/**
 * Contact Javascript actions
 *
 * @category   Ajax
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var ContactCallback = {
    updatecontact: function(response) {
        if (response[0]['css'] == 'notice-message') {
            getDG('contacts_datagrid');
            stopAction();
        }
        showResponse(response);
    },

    updatereply: function(response) {
        if (response[0]['css'] == 'notice-message') {
            selectedRow.getElement('label').set({style:'font-weight:normal'});
            stopAction();
        }
        showResponse(response);
    },

    deleterecipient: function(response) {
        if (response[0]['css'] == 'notice-message') {
            _('recipient_datagrid').deleteItem();          
            getDG();
            stopAction();
        }
        showResponse(response);
    },

    insertrecipient: function(response) {
        if (response[0]['css'] == 'notice-message') {
            _('recipient_datagrid').addItem();
            _('recipient_datagrid').setCurrentPage(0);
            getDG();
            stopAction();
        }
        showResponse(response);
    },

    updaterecipient: function(response) {
        if (response[0]['css'] == 'notice-message') {
            getDG();
            stopAction();
        }
        showResponse(response);
    },

    updateproperties: function(response) {
        showResponse(response);
    },

    deletecontact: function(response) {
        if (response[0]['css'] == 'notice-message') {
            _('contacts_datagrid').deleteItem();          
            getDG('contacts_datagrid');
            stopAction();
        }
        showResponse(response);
    },

    sendemail: function(response) {
        if (response[0]['css'] == 'notice-message') {
            newEmail();
        }
        showResponse(response);
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
    switch(currentAction) {
    case 'Recipients':
        _('id').value      = 0;
        _('name').value    = '';
        _('email').value   = '';
        _('tel').value     = '';
        _('fax').value     = '';
        _('mobile').value  = '';
        _('inform_type').value  = 0;
        _('visible').value = 1;
        unselectDataGridRow();
        _('name').focus();
        break;
    case 'Contacts':
        _('id').value      = 0;
        _('contact_ip').set('html', '');
        _('name').value    = '';
        _('email').value   = '';
        _('company').value = '';
        _('url').value     = '';
        _('tel').value     = '';
        _('fax').value     = '';
        _('mobile').value  = '';
        _('address').value = '';
        _('rid').selectedIndex = -1;
        _('subject').value = '';
        _('message').value = '';
        _('btn_save_send').hide();
        _('tr_attachment').hide();
        _('btn_save').hide();
        _('btn_cancel').hide();
        unselectDataGridRow();
        _('name').focus();
        break;
    case 'Reply':
        _('id').value      = 0;
        _('name').value    = '';
        _('email').value   = '';
        _('subject').value = '';
        _('message').value = '';
        _('reply').value   = '';
        _('reply').readOnly = true;
        _('btn_save_send').hide();
        _('btn_save').hide();
        _('btn_cancel').hide();
        unselectDataGridRow();
    }
}

/**
 * Edit a Contact
 *
 */
function editContact(element, id)
{
    currentAction = 'Contacts';
    _('legend_title').innerHTML = messageDetail_title;
    if (cacheContactForm != null) {
        _('c_work_area').innerHTML = cacheContactForm;
    }

    selectDataGridRow(element.parentNode.parentNode);

    var contact = ContactAjax.callSync('getcontact', id);
    _('id').value      = contact['id'];
    _('contact_ip').set('html', contact['ip']);
    _('name').value    = contact['name'];
    _('email').value   = contact['email'];
    _('company').value = contact['company'];
    _('url').value     = contact['url'];
    _('tel').value     = contact['tel'];
    _('fax').value     = contact['fax'];
    _('mobile').value  = contact['mobile'];
    _('address').value = contact['address'];
    _('rid').value     = contact['recipient'];
    _('subject').value = contact['subject'].defilter();
    _('message').value = contact['msg_txt'].defilter();
    _('btn_save_send').hide();
    _('btn_save').show('inline');
    _('btn_cancel').show('inline');

    if (contact['attachment']) {
        _('attachment').href = dataURL + contact['attachment'];
        _('attachment').set('html', contact['attachment']);
        _('tr_attachment').show();
    } else {
        _('tr_attachment').hide();
    }
}

/**
 * Edit Poll Answers
 */
function editReply(element, id)
{
    if (cacheContactForm == null) {
        cacheContactForm = _('c_work_area').innerHTML;
    }

    selectDataGridRow(element.parentNode.parentNode);

    if (cacheReplyForm == null) {
        cacheReplyForm = ContactAjax.callSync('replyui');
    }
    currentAction = 'Reply';

    selectedContact = id;
    _('legend_title').innerHTML = contactReply_title;
    _('c_work_area').innerHTML = cacheReplyForm;
    var replyData = ContactAjax.callSync('getreply', selectedContact);
    _('id').value      = replyData['id'];
    _('name').value    = replyData['name'];
    _('email').value   = replyData['email'];
    _('subject').value = replyData['subject'].defilter();
    _('message').value = replyData['msg_txt'].defilter();
    _('reply').value   = replyData['reply'].defilter();
    _('btn_save').show('inline');
    _('btn_cancel').show('inline');
    _('btn_save_send').show('inline');
    _('reply').readOnly = Boolean(replyData['readonly']);
    _('reply').focus();
}

/**
 * Update a Contact
 */
function updateContact(send_reply)
{
    switch(currentAction) {
    case 'Contacts':
        ContactAjax.callAsync('updatecontact',
                        _('id').value,
                        _('name').value,
                        _('email').value,
                        _('company').value,
                        _('url').value,
                        _('tel').value,
                        _('fax').value,
                        _('mobile').value,
                        _('address').value,
                        _('rid').value,
                        _('subject').value,
                        _('message').value);
        break;
    case 'Reply':
        ContactAjax.callAsync('updatereply',
                        _('id').value,
                        _('reply').value,
                        send_reply);
        break;
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

/**
 * Get contacts
 *
 */
function getContacts(name, offset, reset)
{
    var result = ContactAjax.callSync('getcontacts', _('recipient_filter').value, offset);
    if (reset) {
        _(name).setCurrentPage(0);
        var total = ContactAjax.callSync('getcontactscount', _('recipient_filter').value);
    }
    resetGrid(name, result, total);
}

/**
 * Edit a Recipient
 *
 */
function editRecipient(element, id)
{
    currentAction = 'Recipients';
    selectDataGridRow(element.parentNode.parentNode);
    var recipient = ContactAjax.callSync('getrecipient', id);
    _('id').value      = recipient['id'];
    _('name').value    = recipient['name'].defilter();
    _('email').value   = recipient['email'];
    _('tel').value     = recipient['tel'];
    _('fax').value     = recipient['fax'];
    _('mobile').value  = recipient['mobile'];
    _('inform_type').value = recipient['inform_type'];
    _('visible').value = recipient['visible'];
}

/**
 * Add/Update a Recipient
 */
function updateRecipient()
{
    if (_('name').value.blank() ||
        _('email').value.blank() ||
        !isValidEmail(_('email').value.trim())) {
        alert(incompleteRecipientFields);
        return;
    }

    if(_('id').value == 0) {
        ContactAjax.callAsync('insertrecipient',
                        _('name').value,
                        _('email').value,
                        _('tel').value,
                        _('fax').value,
                        _('mobile').value,
                        _('inform_type').value,
                        _('visible').value);
    } else {
        ContactAjax.callAsync('updaterecipient',
                        _('id').value,
                        _('name').value,
                        _('email').value,
                        _('tel').value,
                        _('fax').value,
                        _('mobile').value,
                        _('inform_type').value,
                        _('visible').value);
    }
}

/**
 * Delete a Recipient
 */
function deleteRecipient(element, id)
{
    stopAction();
    selectDataGridRow(element.parentNode.parentNode);
    if (confirm(confirmRecipientDelete)) {
        ContactAjax.callAsync('deleterecipient', id);
    }
    unselectDataGridRow();
}

/**
 * Update the properties
 *
 */
function updateProperties()
{
    ContactAjax.callAsync('updateproperties',
                        _('use_antispam').value,
                        _('email_format').value,
                        _('enable_attachment').value,
                        getEditorValue('comments'));
}

/**
 * Switches between two UIs for Email target
 */
function switchEmailTarget(value)
{
    switch (value) {
        case '1':
            if (_('batch_mail').isDisplayed()) break;
            _('free_mail').hide();
            _('batch_mail').show();
            break;
        case '2':
            if (_('free_mail').isDisplayed()) break;
            _('batch_mail').hide();
            _('free_mail').show();
            break;
    }
}

/**
 * Updates users combo according to selected group
 */
function updateUsers(group)
{
    if (group == '0') {
        _('users').setValue(0);
        group = false;
    }
    var users = ContactAjax.callSync('getusers', group);
    _('users').options.length = 0;
    _('users').options[0] = new Option(lblAllGroupUsers, 0);
    users.each(function(user, i) {
        _('users').options[_('users').options.length] = new Option(user['nickname'], user['id']);
    });
}

/**
 * Clears the form for a new Email
 */
function newEmail()
{
    _('groups').value = 0;
    _('users').value = 0;
    _('to').value = '';
    _('cc').value = '';
    _('bcc').value = '';
    _('from').value = '';
    _('subject').value = '';
    _('message').value = '';
    _('filename').value = '';
    _('frm_file').reset();

    _('attachment').show();
    _('btn_upload').show();
    _('attach_loading').hide();
    _('btn_attach').hide();
    toggleDisableForm(false);
}

/**
 * Disables/Enables form elements
 */
function toggleDisableForm(disabled)
{
    _('options_1').disabled   = disabled;
    _('options_2').disabled   = disabled;
    _('to').disabled          = disabled;
    _('cc').disabled          = disabled;
    _('bcc').disabled         = disabled;
    _('groups').disabled      = disabled;
    _('users').disabled       = disabled;
    _('subject').disabled     = disabled;
    _('message').disabled     = disabled;
    _('btn_send').disabled    = disabled;
    _('btn_preview').disabled = disabled;
    _('btn_new').disabled     = disabled;
}

/**
 * Uploads the attachment file
 */
function uploadFile() {
    showWorkingNotification();
    var iframe = new Element('iframe', {id:'ifrm_upload', name:'ifrm_upload'});
    _('mailer').adopt(iframe);
    _('attachment').hide();
    _('btn_upload').hide();
    _('attach_loading').show();
    toggleDisableForm(true);
    _('frm_file').submit();
}

/**
 * Sets the uploaded file as attachment
 */
function onUpload(response) {
    hideWorkingNotification();
    toggleDisableForm(false);
    if (response.type === 'error') {
        alert(response.message);
        _('frm_file').reset();
        _('btn_upload').show();
        _('attachment').show();
    } else {
        _('filename').value = response.filename;
        _('file_link').set('html', response.filename);
        _('file_size').set('html', response.filesize);
        _('btn_attach').show();
        _('attachment').hide();
    }
    _('attach_loading').hide();
    _('ifrm_upload').destroy();
}

/**
 * Removes the attachment
 */
function removeAttachment() {
    _('filename').value = '';
    _('frm_file').reset();
    _('btn_attach').hide();
    _('file_link').set('html', '');
    _('file_size').set('html', '');
    _('btn_upload').show();
    _('attachment').show();
}

/**
 * Opens popup window with a preview of the message body
 */
function previewMessage()
{
    var preview  = ContactAjax.callSync('getmessagepreview', getEditorValue('message')),
        width    = 750,
        height   = 500,
        docDim   = document.getSize(),
        left     = (docDim.x - width) / 2,
        top      = (docDim.y - height) / 2,
        specs    = 'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top,
        popup    = window.open('about:blank', '', specs, true);
    popup.document.write(preview);
}

/**
 * Sends the Email
 */
function sendEmail()
{
    if (_('options_1').checked) {
        if (_('users').options.length <= 1) {
            alert(groupHasNoUser);
            _('groups').focus();
            return;
        }
        var target = {'group': _('groups').value,
                      'user' : _('users').value};
    } else {
        // Already we have isValidEmail() but validation becomes 
        // too complicated in case of 3 fields (to, cc, bcc) so let server do the job
        if (_('to').value.blank() &&
            _('cc').value.blank() &&
            _('bcc').value.blank())
        {
            alert(incompleteMailerFields);
            _('to').focus();
            return;
        }
        var target = {'to' : _('to').value,
                      'cc' : _('cc').value,
                      'bcc': _('bcc').value};
    }

    if (_('subject').value.blank()) {
        alert(incompleteMailerFields);
        _('subject').focus();
        return;
    }

    var body = getEditorValue('message');
    if (body.blank()) {
        alert(incompleteMailerFields);
        _('message').focus();
        return;
    }

    ContactAjax.callAsync('sendemail', target, _('subject').value, body, _('filename').value);
}

var ContactAjax = new JawsAjax('Contact', ContactCallback),
    cacheContactForm = null,
    cacheReplyForm = null,
    currentAction = null,
    selectedRow = null,
    selectedRowColor = null;
