/**
 * Contact Javascript actions
 *
 * @category   Ajax
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var ContactCallback = {
    UpdateContact: function(response) {
        if (response[0]['type'] == 'response_notice') {
            getDG('contacts_datagrid');
            stopAction();
        }
        ContactAjax.showResponse(response);
    },

    UpdateReply: function(response) {
        if (response[0]['type'] == 'response_notice') {
            selectedRow.getElement('label').set({style:'font-weight:normal'});
            stopAction();
        }
        ContactAjax.showResponse(response);
    },

    DeleteRecipient: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('recipient_datagrid')[0].deleteItem();          
            getDG();
            stopAction();
        }
        ContactAjax.showResponse(response);
    },

    InsertRecipient: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('recipient_datagrid')[0].addItem();
            $('recipient_datagrid')[0].setCurrentPage(0);
            getDG();
            stopAction();
        }
        ContactAjax.showResponse(response);
    },

    UpdateRecipient: function(response) {
        if (response[0]['type'] == 'response_notice') {
            getDG();
            stopAction();
        }
        ContactAjax.showResponse(response);
    },

    UpdateProperties: function(response) {
        ContactAjax.showResponse(response);
    },

    DeleteContact: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('contacts_datagrid')[0].deleteItem();          
            getDG('contacts_datagrid');
            stopAction();
        }
        ContactAjax.showResponse(response);
    },

    SendEmail: function(response) {
        if (response[0]['type'] == 'response_notice') {
            newEmail();
        }
        ContactAjax.showResponse(response);
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
        $('#id').val(0);
        $('#name').val('');
        $('#email').val('');
        $('#tel').val('');
        $('#fax').val('');
        $('#mobile').val('');
        $('#inform_type').val(0);
        $('#visible').val(1);
        unselectDataGridRow();
        $('name').focus();
        break;
    case 'Contacts':
        $('#id').val(0);
        $('#contact_ip').html('');
        $('#name').val('');
        $('#email').val('');
        $('#company').val('');
        $('#url').val('');
        $('#tel').val('');
        $('#fax').val('');
        $('#mobile').val('');
        $('#address').val('');
        $('#rid').prop('selectedIndex', -1);
        $('#subject').val('');
        $('#message').val('');
        $('#btn_save_send').hide();
        $('#tr_attachment').hide();
        $('#btn_save').hide();
        $('#btn_cancel').hide();
        unselectDataGridRow();
        $('#name').focus();
        break;
    case 'Reply':
        $('#id').val(0);
        $('#name').val('');
        $('#email').val('');
        $('#subject').val('');
        $('#message').val('');
        $('#reply').val('');
        $('#reply').prop('readonly', true);
        $('#btn_save_send').hide();
        $('#btn_save').hide();
        $('#btn_cancel').hide();
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
    $('#legend_title').html(messageDetail_title);
    if (cacheContactForm != null) {
        $('#c_work_area').html(cacheContactForm);
    }

    selectDataGridRow(element.parentNode.parentNode);

    var contact = ContactAjax.callSync('GetContact', id);
    $('#id').val(contact['id']);
    $('#contact_ip').html(contact['ip']);
    $('#name').val(contact['name']);
    $('#email').val(contact['email']);
    $('#company').val(contact['company']);
    $('#url').val(contact['url']);
    $('#tel').val(contact['tel']);
    $('#fax').val(contact['fax']);
    $('#mobile').val(contact['mobile']);
    $('#address').val(contact['address']);
    $('#rid').val(contact['recipient']);
    $('#subject').val(contact['subject'].defilter());
    $('#message').val(contact['msg_txt'].defilter());
    $('#btn_save_send').hide();
    $('#btn_save').css('display', 'inline');
    $('#btn_cancel').css('display', 'inline');

    if (contact['attachment']) {
        $('#attachment').href = dataURL + contact['attachment'];
        $('#attachment').html(contact['attachment']);
        $('#tr_attachment').show();
    } else {
        $('#tr_attachment').hide();
    }
}

/**
 * Edit Poll Answers
 */
function editReply(element, id)
{
    if (cacheContactForm == null) {
        cacheContactForm = $('#c_work_area').html();
    }

    selectDataGridRow(element.parentNode.parentNode);

    if (cacheReplyForm == null) {
        cacheReplyForm = ContactAjax.callSync('ReplyUI');
    }
    currentAction = 'Reply';

    selectedContact = id;
    $('#legend_title').html(contactReply_title);
    $('#c_work_area').html(cacheReplyForm);
    var replyData = ContactAjax.callSync('GetReply', selectedContact);
    $('#id').val(replyData['id']);
    $('#name').val(replyData['name']);
    $('#email').val(replyData['email']);
    $('#subject').val(replyData['subject'].defilter());
    $('#message').val(replyData['msg_txt'].defilter());
    $('#reply').val(replyData['reply'].defilter());
    $('#btn_save').css('display', 'inline');
    $('#btn_cancel').css('display', 'inline');
    $('#btn_save_send').css('display', 'inline');
    $('#reply').prop('readonly', Boolean(replyData['readonly']));
    $('#reply').focus();
}

/**
 * Update a Contact
 */
function updateContact(send_reply)
{
    switch(currentAction) {
    case 'Contacts':
        ContactAjax.callAsync(
            'UpdateContact', [
                $('#id').val(),
                $('#name').val(),
                $('#email').val(),
                $('#company').val(),
                $('#url').val(),
                $('#tel').val(),
                $('#fax').val(),
                $('#mobile').val(),
                $('#address').val(),
                $('#rid').val(),
                $('#subject').val(),
                $('#message').val()
            ]
        );
        break;
    case 'Reply':
        ContactAjax.callAsync(
            'UpdateReply', [
                $('#id').val(),
                $('#reply').val(),
                send_reply
            ]
        );
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
        ContactAjax.callAsync('DeleteContact', id);
    }
    unselectDataGridRow();
}

/**
 * Get contacts
 *
 */
function getContacts(name, offset, reset)
{
    var result = ContactAjax.callSync('GetContacts', [$('#recipient_filter').val(), offset]);
    if (reset) {
        $(name)[0].setCurrentPage(0);
        var total = ContactAjax.callSync('GetContactsCount', $('#recipient_filter').val());
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
    var recipient = ContactAjax.callSync('GetRecipient', id);
    $('#id').val(recipient['id']);
    $('#name').val(recipient['name'].defilter());
    $('#email').val(recipient['email']);
    $('#tel').val(recipient['tel']);
    $('#fax').val(recipient['fax']);
    $('#mobile').val(recipient['mobile']);
    $('#inform_type').val(recipient['inform_type']);
    $('#visible').val(recipient['visible']);
}

/**
 * Add/Update a Recipient
 */
function updateRecipient()
{
    if (!$('name').val() ||
        !$('email').val() ||
        !isValidEmail($('email').value.trim())) {
        alert(incompleteRecipientFields);
        return;
    }

    if($('id').value == 0) {
        ContactAjax.callAsync(
            'InsertRecipient', [
                $('#name').val(),
                $('#email').val(),
                $('#tel').val(),
                $('#fax').val(),
                $('#mobile').val(),
                $('#inform_type').val(),
                $('#visible').val()
            ]
        );
    } else {
        ContactAjax.callAsync(
            'UpdateRecipient', [
                $('#id').val(),
                $('#name').val(),
                $('#email').val(),
                $('#tel').val(),
                $('#fax').val(),
                $('#mobile').val(),
                $('#inform_type').val(),
                $('#visible').val()
            ]
        );
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
        ContactAjax.callAsync('DeleteRecipient', id);
    }
    unselectDataGridRow();
}

/**
 * Update the properties
 *
 */
function updateProperties()
{
    ContactAjax.callAsync(
        'UpdateProperties', [
            $('#use_antispam').val(),
            $('#email_format').val(),
            $('#enable_attachment').val(),
            getEditorValue('comments')
        ]
    );
}

/**
 * Switches between two UIs for Email target
 */
function switchEmailTarget(value)
{
    switch (value) {
        case '1':
            if ($('#batch_mail').css('display') != 'none') {
                break;
            }
            $('#free_mail').hide();
            $('#batch_mail').show();
            break;
        case '2':
            if ($('#free_mail').css('display') != 'none') {
                break;
            }
            $('#batch_mail').hide();
            $('#free_mail').show();
            break;
    }
}

/**
 * Updates users combo according to selected group
 */
function updateUsers(group)
{
    if (group == '0') {
        $('users').setValue(0);
        group = false;
    }
    var users = ContactAjax.callSync('GetUsers', group);
    $('users').options.length = 0;
    $('users').options[0] = new Option(lblAllGroupUsers, 0);
    users.each(function(user, i) {
        $('users').options[$('users').options.length] = new Option(user['nickname'], user['id']);
    });
}

/**
 * Clears the form for a new Email
 */
function newEmail()
{
    $('#groups').val(0);
    $('#users').val(0);
    $('#to').val('');
    $('#cc').val('');
    $('#bcc').val('');
    $('#from').val('');
    $('#subject').val('');
    $('#message').val('');
    $('#filename').val('');
    $('frm_file').reset();

    $('#attachment').show();
    $('#btn_upload').show();
    $('#attach_loading').hide();
    $('#btn_attach').hide();
    toggleDisableForm(false);
}

/**
 * Disables/Enables form elements
 */
function toggleDisableForm(disabled)
{
    $('#options_1').prop('disabled', disabled);
    $('#options_2').prop('disabled', disabled);
    $('#to').prop('disabled', disabled);
    $('#cc').prop('disabled', disabled);
    $('#bcc').prop('disabled', disabled);
    $('#groups').prop('disabled', disabled);
    $('#users').prop('disabled', disabled);
    $('#subject').prop('disabled', disabled);
    $('#message').prop('disabled', disabled);
    $('#btn_send').prop('disabled', disabled);
    $('#btn_preview').prop('disabled', disabled);
    $('#btn_new').prop('disabled', disabled);
}

/**
 * Uploads the attachment file
 */
function uploadFile() {
    showWorkingNotification();
    var iframe = new Element('iframe', {id:'ifrm_upload', name:'ifrm_upload'});
    $('#mailer').append(iframe);
    $('#attachment').hide();
    $('#btn_upload').hide();
    $('#attach_loading').show();
    toggleDisableForm(true);
    $('#frm_file').submit();
}

/**
 * Sets the uploaded file as attachment
 */
function onUpload(response) {
    hideWorkingNotification();
    toggleDisableForm(false);
    if (response.type === 'error') {
        alert(response.message);
        $('#frm_file').reset();
        $('#btn_upload').show();
        $('#attachment').show();
    } else {
        $('#filename').val(response.filename);
        $('#file_link').html(response.filename);
        $('#file_size').html(response.filesize);
        $('#btn_attach').show();
        $('#attachment').hide();
    }
    $('#attach_loading').hide();
    $('#ifrm_upload').remove();
}

/**
 * Removes the attachment
 */
function removeAttachment() {
    $('#filename').val('');
    $('#frm_file')[0].reset();
    $('#btn_attach').hide();
    $('#file_link').html('');
    $('#file_size').html('');
    $('#btn_upload').show();
    $('#attachment').show();
}

/**
 * Opens popup window with a preview of the message body
 */
function previewMessage()
{
    var preview  = ContactAjax.callSync('GetMessagePreview', getEditorValue('message')),
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
    if ($('options_1').checked) {
        if ($('users').options.length <= 1) {
            alert(groupHasNoUser);
            $('groups').focus();
            return;
        }
        var target = {'group': $('#groups').val(),
                      'user' : $('#users').val()};
    } else {
        // Already we have isValidEmail() but validation becomes 
        // too complicated in case of 3 fields (to, cc, bcc) so let server do the job
        if (!$('#to').val() &&
            !$('#cc').val() &&
            !$('#bcc').val())
        {
            alert(incompleteMailerFields);
            $('#to').focus();
            return;
        }
        var target = {'to' : $('#to').val(),
                      'cc' : $('#cc').val(),
                      'bcc': $('#bcc').val()};
    }

    if (!$('#subject').val()) {
        alert(incompleteMailerFields);
        $('#subject').focus();
        return;
    }

    var body = getEditorValue('message');
    if (body.blank()) {
        alert(incompleteMailerFields);
        $('message').focus();
        return;
    }

    ContactAjax.callAsync(
        'SendEmail',
        [target, $('#subject').val(), body, $('#filename').val()]
    );
}

var ContactAjax = new JawsAjax('Contact', ContactCallback),
    cacheContactForm = null,
    cacheReplyForm = null,
    currentAction = null,
    selectedRow = null,
    selectedRowColor = null;
