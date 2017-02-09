/**
 * Contact Javascript actions
 *
 * @category   Ajax
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var ContactCallback = {
    UpdateContact: function(response) {
        if (response['type'] == 'alert-success') {
            getDG('contacts_datagrid');
            stopAction();
        }
        ContactAjax.showResponse(response);
    },

    DeleteRecipient: function(response) {
        if (response['type'] == 'alert-success') {
            $('#recipient_datagrid')[0].deleteItem();
            getDG();
            stopAction();
        }
        ContactAjax.showResponse(response);
    },

    InsertRecipient: function(response) {
        if (response['type'] == 'alert-success') {
            $('#recipient_datagrid')[0].addItem();
            $('#recipient_datagrid')[0].setCurrentPage(0);
            getDG();
            stopAction();
        }
        ContactAjax.showResponse(response);
    },

    UpdateRecipient: function(response) {
        if (response['type'] == 'alert-success') {
            getDG();
            stopAction();
        }
        ContactAjax.showResponse(response);
    },

    UpdateProperties: function(response) {
        ContactAjax.showResponse(response);
    },

    DeleteContact: function(response) {
        if (response['type'] == 'alert-success') {
            $('#contacts_datagrid')[0].deleteItem();
            getDG('contacts_datagrid');
            stopAction();
        }
        ContactAjax.showResponse(response);
    },

    SendEmail: function(response) {
        if (response['type'] == 'alert-success') {
            newEmail();
        }
        ContactAjax.showResponse(response);
    }
};

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
        $(selectedRow).css('background-color', selectedRowColor);
    }
    selectedRowColor = $(rowElement).css('background-color');
    $(rowElement).css('background-color', '#ffffcc');
    selectedRow = rowElement;
}

/**
 * Unselect DataGrid row
 *
 */
function unselectDataGridRow()
{
    if (selectedRow) {
        $(selectedRow).css('background-color', selectedRowColor);
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
        $('#name').focus();
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
    $('#legend_title').html(jaws.gadgets.Contact.messageDetail_title);
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
        $('#attachment').attr('href', jaws.gadgets.Contact.dataURL + contact['attachment']);
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
    $('#legend_title').html(jaws.gadgets.Contact.contactReply_title);
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
    ContactAjax.callAsync(
        'UpdateContact',
        Object.assign(
            $.unserialize($('#contact_ui input,#contact_ui select,#contact_ui textarea').serialize()),
            send_reply? {'reply_sent': 1} : {}
        )
    );
}

/**
 * Delete contact
 *
 */
function deleteContact(element, id)
{
    stopAction();
    selectDataGridRow($(element).parent().parent());
    if (confirm(jaws.gadgets.Contact.confirmContactDelete)) {
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
        $('#' + name)[0].setCurrentPage(0);
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
    if (!$('#name').val() ||
        !$('#email').val() ||
        !isValidEmail($('#email')[0].value.trim())) {
        alert(jaws.gadgets.Contact.incompleteRecipientFields);
        return;
    }

    if ($('#id').val() == 0) {
        ContactAjax.callAsync(
            'InsertRecipient',
            $.unserialize($('#recipient input,#recipient select,#recipient textarea').serialize())
        );
    } else {
        ContactAjax.callAsync(
            'UpdateRecipient',
            $.unserialize($('#recipient input,#recipient select,#recipient textarea').serialize())
        );
    }
}

/**
 * Delete a Recipient
 */
function deleteRecipient(element, id)
{
    stopAction();
    selectDataGridRow($(element).parent().parent());
    if (confirm(jaws.gadgets.Contact.confirmRecipientDelete)) {
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
            getEditorValue('#comments')
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
        $('#users').val(0);
        group = false;
    }
    var users = ContactAjax.callSync('GetUsers', group);
    $('#users').empty().append($('<option>').html(jaws.gadgets.Contact.lblAllGroupUsers).val(0));
    $.each(users, function(i, user) {
        $('#users').append($('<option>').html(user['nickname']).val(user['id']));
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
    $('#frm_file')[0].reset();

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
    var iframe = $('<iframe>').attr({id:'ifrm_upload', name:'ifrm_upload'});
    $('#mailer').append(iframe);
    $('#attachment').hide();
    $('#btn_upload').hide();
    $('#attach_loading').show();
    toggleDisableForm(true);
    $('#frm_file')[0].submit();
}

/**
 * Sets the uploaded file as attachment
 */
function onUpload(response) {
    hideWorkingNotification();
    toggleDisableForm(false);
    if (response.type === 'error') {
        alert(response.message);
        $('#frm_file')[0].reset();
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
    var preview  = ContactAjax.callSync('GetMessagePreview', getEditorValue('#message')),
        width    = 750,
        height   = 500,
        left     = ($(document).width() - width) / 2,
        top      = ($(document).height() - height) / 2,
        specs    = 'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top,
        popup    = window.open('about:blank', '', specs, true);
    popup.document.write(preview);
}

/**
 * Sends the Email
 */
function sendEmail()
{
    if ($('#options_1').prop('checked')) {
        if ($('#users')[0].options.length <= 1) {
            alert(jaws.gadgets.Contact.groupHasNoUser);
            $('#groups')[0].focus();
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
            alert(jaws.gadgets.Contact.incompleteMailerFields);
            $('#to').focus();
            return;
        }
        var target = {'to' : $('#to').val(),
                      'cc' : $('#cc').val(),
                      'bcc': $('#bcc').val()};
    }

    if (!$('#subject').val()) {
        alert(jaws.gadgets.Contact.incompleteMailerFields);
        $('#subject')[0].focus();
        return;
    }

    var body = getEditorValue('#message');
    if (body == '') {
        alert(jaws.gadgets.Contact.incompleteMailerFields);
        $('#message')[0].focus();
        return;
    }

    ContactAjax.callAsync(
        'SendEmail',
        [target, $('#subject').val(), body, $('#filename').val()]
    );
}

$(document).ready(function() {
    switch (jaws.core.mainAction) {
        case 'Contacts':
            currentAction = 'Contacts';
            $('#recipient_filter')[0].selectedIndex = 0;
            initDataGrid('contacts_datagrid', ContactAjax, getContacts);
            break;

        case 'Recipients':
            currentAction = 'Recipients';
            initDataGrid('recipient_datagrid', ContactAjax);
            break;

        case 'Mailer':
            newEmail();
            $('#options_1').prop('checked', true);
            switchEmailTarget(1);
            break;
    }
});


var ContactAjax = new JawsAjax('Contact', ContactCallback),
    cacheContactForm = null,
    cacheReplyForm = null,
    currentAction = null,
    selectedRow = null,
    selectedRowColor = null;
