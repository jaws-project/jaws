/**
 * AddressBook Javascript actions
 *
 * @category   Ajax
 * @package    AddressBook
 */
/**
 * Use async mode, create Callback
 */
var AddressBookCallback = {
    DeleteAddress: function(response) {
        AddressBookAjax.showResponse(response);
        FilterAddress();
    },
    DeleteGroup:  function(response) {
        AddressBookAjax.showResponse(response);
        //ReloadGroups();
    }
}

function AddTellItem(inputObject)
{
    lastID = lastID + 1;
    $('#removeTelButton').show();
    var div = $('#tel_p>div:first').clone();
    div.attr('class', 'tel').attr('id', "tel_" + lastID);
    div.find('select')[0].name = 'tel_type[]';
    div.find('input')[0].name = 'tel_number[]';
    div.find('input')[0].value = '';
    div.find('select')[0].selectedIndex = 0;
    $($(inputObject).parent()).after(div);
}

function AddEmailItem(inputObject)
{
    lastID = lastID + 1;
    $('#removeEmailButton').show();
    var div = $('#email_p>div:first').clone();
    div.attr('class', 'email').attr('id', "email_" + lastID);
    div.find('select')[0].name = 'email_type[]';
    div.find('input')[0].name  = 'email[]';
    div.find('input')[0].value = '';
    div.find('select')[0].selectedIndex = 0;
    $($(inputObject).parent()).after(div);
}

function AddAdrItem(inputObject)
{
    lastID = lastID + 1;
    $('#removeAdrButton').show();
    var div = $('#adr_p>div:first').clone();
    div.attr('class', 'adr').attr('id', "adr_" + lastID);
    div.find('select')[0].name = 'adr_type[]';
    div.find('textarea')[0].name  = 'adr[]';
    div.find('textarea')[0].value = '';
    div.find('select')[0].selectedIndex = 0;
    $($(inputObject).parent()).after(div);
}

function AddUrlItem(inputObject)
{
    lastID = lastID + 1;
    $('#removeUrlButton').show();
    var div = $('#url_p>div:first').clone();
    div.attr('class', 'url').attr('id', "url_" + lastID);
    div.find('input')[0].name  = 'url[]';
    div.find('input')[0].value = '';
    $($(inputObject).parent()).after(div);
}

function RemoveItem(inputObject)
{
    remain = $(inputObject).parent().parent().find('div').length;
    parent = $(inputObject).parent().parent().find('div');
    $(inputObject).parent().remove()
    if (remain == 2) {
        $(parent[0]).find('button')[1].style.display = 'none';
    }
}

/**
 * Get user information and set in address book
 */
function GetUserInfo()
{
    if ($('#addressbook_user_link').val() == 0) {
        return;
    }
    $('#last_refreh_user_link').val($('#addressbook_user_link').val());
    var userInfo = AddressBookAjax.callSync('LoadUserInfo', {'uid': $('#addressbook_user_link').val()});
    $('#addressbook_firstname').val(userInfo['fname']);
    $('#addressbook_lastname').val(userInfo['lname']);
    $('#addressbook_nickname').val(userInfo['nickname']);
    $('#person_image').prop('src', userInfo['avatar']);
    $('#image').val(userInfo['avatar_file_name']);
}


/**
 * Filter AddressBooks and show results
 */
function FilterAddress()
{
    var filterResult = AddressBookAjax.callSync('AddressList', {
            'gid': $('#addressbook_group').val(),
            'term': $('#addressbook_term').val()
        });

    $('#addressbook_result').html(filterResult);
    lastGroup = $('#addressbook_group').val();
    lastTerm = $('#addressbook_term').val();
}

/**
 * Save Address Info
 */
function SaveAddress()
{
    if ($('#addressbook_firstname').val() == '' && $('#addressbook_lastname').val() == '') {
        alert(nameEmptyWarning);
        return;
    }
    $('#edit_addressbook').submit();
}

/**
 * Execute Selected Action In Selected Addresses
 */
function ExAction()
{
    var action = $('#addressbook_gaction').val();
    if (action == 'DeleteAddress') {
        AddressBookAjax.callAsync(
            'DeleteAddress',
            $.unserialize($('#form[name=AddressBookAction]').serialize())
        );
    } else if (action == 'VCardBuild') {
        /*
        AddressBookAjax.callSync(
            'VCardBuild',
            $.unserialize($('#form[name=AddressBookAction]').serialize())
        );
        */
        $('#AddressBookAction').submit();
    } else if (action == 'DeleteGroup') {
        AddressBookAjax.callAsync(
            'DeleteGroup', 
            $.unserialize($('#form[name=AddressBookAction]').serialize())
        );
    }
    return false;
}

/**
 * Add Relation Between Address And Group
 */
function AddAddressToGroup()
{
    if ($('#addressbook_group').val()) {
        $('#addressbook_bonding').submit();
    }
}

function ReloadToggle()
{
    $('#group_p').toggle();
    var mDiv = $('#tel_p').find('div')[0];
    if ($('#tel_p').find('div').length == 1 && $(mDiv).find('input')[0].value == '') {
        $('#tel_p').toggle();
    } else {
        ChangeToggleIcon($('#legend_tel'));
    }

    var mDiv = $('#email_p').find('div')[0];
    if ($('#email_p').find('div').length == 1 && $(mDiv).find('input')[0].value == '') {
        $('#email_p').toggle();
    } else {
        ChangeToggleIcon($('#legend_email'));
    }

    var mDiv = $('#adr_p').find('div')[0];
    if ($('#adr_p').find('div').length == 1 && $(mDiv).find('textarea')[0].value == '') {
        $('#adr_p').toggle();
    } else {
        ChangeToggleIcon($('#legend_adr'));
    }

    var mDiv = $('#url_p').find('div')[0];
    if ($('#url_p').find('div').length == 1 && $(mDiv).find('input')[0].value == '') {
        $('#url_p').toggle();
    } else {
        ChangeToggleIcon($('#legend_urls'));
    }

    if ($('#other_p').find('textarea')[0].value == '') {
        $('#other_p').toggle();
    } else {
        ChangeToggleIcon($('#legend_other'));
    }
}

function ChangeToggleIcon(obj)
{
    if ($(obj).attr('toggle-status') == 'min') {
        $(obj).find("img").attr('src', toggleMin);
        $(obj).attr('toggle-status', 'max');
    } else {
        $(obj).find("img").attr('src', toggleMax);
        $(obj).attr('toggle-status', 'min');
    }
}

/**
 * Uploads the image
 */
function upload()
{
    $("#addressbook_image").append($('<iframe></iframe>').attr({'id': 'ifrm_upload', 'name':'ifrm_upload'}));
    $('#frm_person_image').submit();
}

/**
 * Loads and sets the uploaded image
 */
function onUpload(response)
{
    if (response.type === 'error') {
        alert(response.message);
        $('#frm_person_image')[0].reset();
    } else {
        var filename = response.message + '//time//' + (new Date()).getTime();
        $('#person_image').prop('src', loadImageUrl + filename);
        $('#image').val(response.message);
    }
    $('#ifrm_upload').remove();
}


/**
 * Removes the image
 */
function removeImage()
{
    $('#image').val('');
    $('#frm_person_image')[0].reset();
    $('#person_image').prop('src', baseSiteUrl + '/gadgets/AddressBook/Resources/images/photo128px.png?' + (new Date()).getTime());
}

function toggleCheckboxes(checkStatus)
{
    $('.table-checkbox').each(function(el) { el.checked = checkStatus; });
}


var AddressBookAjax = new JawsAjax('AddressBook', AddressBookCallback);
var lastGroup = 0;
var lastTerm = '';
