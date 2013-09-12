/**
 * AddressBook Javascript actions
 *
 * @category   Ajax
 * @package    AddressBook
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 */

function AddTellItem()
{
    lastID = lastID + 1;
    $('removeTelButton').style.display = 'inline'
    var div = $('tel_p').getElementsByTagName('div')[0].cloneNode(true);
    div.className = 'tel';
    div.id = "tel_" + lastID;
    div.getElementsByTagName('select')[0].name = 'tel_type['+lastID+']';
    div.getElementsByTagName('input')[0].name  = 'tel_number['+lastID+']';
    div.getElementsByTagName('input')[0].value = '';
    div.getElementsByTagName('select')[0].selectedIndex = 0;
    $('tel_p').appendChild(div);
}

function AddEmailItem()
{
    lastID = lastID + 1;
    $('removeEmailButton').style.display = 'inline'
    var div = $('email_p').getElementsByTagName('div')[0].cloneNode(true);
    div.className = 'email';
    div.id = "email_" + lastID;
    div.getElementsByTagName('select')[0].name = 'email_type['+lastID+']';
    div.getElementsByTagName('input')[0].name  = 'email['+lastID+']';
    div.getElementsByTagName('input')[0].value = '';
    div.getElementsByTagName('select')[0].selectedIndex = 0;
    $('email_p').appendChild(div);
}

function AddAdrItem()
{
    lastID = lastID + 1;
    $('removeAdrButton').style.display = 'inline'
    var div = $('adr_p').getElementsByTagName('div')[0].cloneNode(true);
    div.className = 'adr';
    div.id = "adr_" + lastID;
    div.getElementsByTagName('select')[0].name = 'adr_type['+lastID+']';
    div.getElementsByTagName('textarea')[0].name  = 'adr['+lastID+']';
    div.getElementsByTagName('textarea')[0].value = '';
    div.getElementsByTagName('select')[0].selectedIndex = 0;
    $('adr_p').appendChild(div);
}

function AddUrlItem()
{
    lastID = lastID + 1;
    $('removeUrlButton').style.display = 'inline'
    var div = $('url_p').getElementsByTagName('div')[0].cloneNode(true);
    div.className = 'url';
    div.id = "url_" + lastID;
    div.getElementsByTagName('input')[0].name  = 'url['+lastID+']';
    div.getElementsByTagName('input')[0].value = '';
    $('url_p').appendChild(div);
}

function RemoveItem(inputObject)
{
    remain = $(inputObject).parentNode.parentNode.getElementsByTagName('div').length;
    parent = $(inputObject).parentNode.parentNode.getElementsByTagName('div');
    Element.destroy($(inputObject).parentNode);
    if (remain == 2) {
        parent[0].getElementsByTagName('button')[1].style.display = 'none';
    }
}

/**
 * Get user information and replace with current data
 */
function GetUserInfo()
{
    if ($('addressbook_user_link').value == 0) {
        return;
    }
    $('last_refreh_user_link').value = $('addressbook_user_link').value;
    var userInfo = AddressBookAjax.callSync('GetUserInfo', {'uid': $('addressbook_user_link').value});
    $('addressbook_firstname').value = userInfo['fname'];
    $('addressbook_lastname').value = userInfo['lname'];
    $('addressbook_nickname').value = userInfo['nickname'];
    $('person_image').src = userInfo['avatar'];
}

var AddressBookAjax = new JawsAjax('AddressBook');