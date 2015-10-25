/**
 * Menu JS actions
 *
 * @category    Ajax
 * @package     Menu
 * @author      Jonathan Hernandez <ion@gluch.org.mx>
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2005-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var MenuCallback = {

    UpdateGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('#group_'+$('#gid').val()).children('a').first().html($('#title').val());
            stopAction();
        }
        MenuAjax.showResponse(response);
    }
}

function isValidURL(url) {
    return (/^(((ht|f)tp(s?))\:\/\/).*$/.test(url));
}

function changeMenuGroup(gid, mid) {
    mid = ((mid == null)? $('#mid').val() : mid);
    getParentMenus(gid, mid);
    changeMenuParent(0);
}

function changeMenuParent(pid) {
    setRanksCombo($('#gid').val(), pid);
}

function AddNewMenuGroup(gid) {
    var mainDiv = $('<div></div>');
    var div = $('group_1').find('div').clone(true);
    mainDiv.addClass('menu_groups');
    mainDiv.attr('id', "group_" + gid);
    mainDiv.append(div);
    $('menus_trees').append(mainDiv);
    var links = mainDiv.find('a');
    links[0].href = 'javascript: editGroup(' + gid + ');';
    links.html($('#title').val());
    links[1].href = 'javascript: addMenu(' + gid + ', 0);';
}

/**
 *
 */
function AddNewMenuItem(gid, pid, mid, rank) {
    var mainDiv = $('<div></div>');
    var div = $('group_1').find('div').clone(true);
    mainDiv.addClass('menu_levels');
    mainDiv.attr('id', "menu_" + mid);
    mainDiv.append(div);

    if (pid == 0) {
        var parentNode = $('#group_' + gid);
    } else {
        var parentNode = $('#menu_' + pid);
    }

    parentNode.append(mainDiv);
    //set ranking
    var menu_elements = Array.from(parentNode.children('.menu_levels'));
    var oldRank = menu_elements.indexOf($('#menu_' + mid));
    if (rank < oldRank) {
        parentNode.insertBefore($('#menu_' + mid), menu_elements[rank - 1]);
    }

    //--
    var links = mainDiv.find('a');
    links[0].href = 'javascript: editMenu(' + mid + ');';
    links.html($('#title').val());
    links[1].href = 'javascript: addMenu(' + gid + ', ' + mid + ');';
    var images = mainDiv.find('img');
    images[0].src = menuImageSrc;
    // hide menu actions
    mainDiv.find('div')[2].css('visibility' , 'hidden');
}

/**
 * Saves data / changes
 */
function saveMenus()
{
    if (currentAction == 'Groups') {
        if (!$('#title').val()) {
            alert(incompleteFields);
            return false;
        }
        cacheMenuForm = null;
        if (selectedGroup == null) {
            var response = MenuAjax.callSync(
                'InsertGroup', [
                    $('#title').val(),
                    $('#title_view').val(),
                    $('#published').val()
                ]
            );
            if (response[0]['type'] == 'response_notice') {
                var gid = response[0]['data'];
                AddNewMenuGroup(gid);
                stopAction();
            }
            MenuAjax.showResponse(response);
        } else {
            MenuAjax.callAsync(
                'UpdateGroup', [
                    $('#gid').val(),
                    $('#title').val(),
                    $('#title_view').val(),
                    $('#published').val()
                ]
            );
        }
    } else {
        if (!$('#title').val() || ($('#references').prop('selectedIndex') == -1)) {
            alert(incompleteFields);
            return false;
        }
        if (selectedMenu == null) {
            var response = MenuAjax.callSync(
                'InsertMenu', [
                    $('#pid').val(),
                    $('#gid').val(),
                    $('#type').val(),
                    aclInfo,
                    $('#title').val(),
                    encodeURI($('#url').val()),
                    $('#url_target').val(),
                    $('#rank').val(),
                    $('#published').val(),
                    $('#imagename').val()
                ]
            );
            if (response[0]['type'] == 'response_notice') {
                var mid = response[0]['text'].substr(0, response[0]['text'].indexOf('%%'));
                response[0]['text'] = response[0]['text'].substr(response[0]['text'].indexOf('%%')+2);
                AddNewMenuItem($('#gid').val(), $('#pid').val(), mid, $('#rank').val());
                stopAction();
            }
            MenuAjax.showResponse(response);
        } else {
            var response = MenuAjax.callSync(
                'UpdateMenu', [
                    $('#mid').val(),
                    $('#pid').val(),
                    $('#gid').val(),
                    $('#type').val(),
                    aclInfo,
                    $('#title').val(),
                    encodeURI($('#url').val()),
                    $('#url_target').val(),
                    $('#rank').val(),
                    $('#published').val(),
                    $('#imagename').val()
                ]
            );
            if (response[0]['type'] == 'response_notice') {
                $('#menu_'+$('#mid').val()).children('a').first().html($('#title').val());
                if ($('#pid').val() == 0) {
                    var new_parentNode = $('#group_'+$('#gid').val());
                } else {
                    var new_parentNode = $('#menu_'+$('#pid').val());
                }

                var menu_elements = Array.from(new_parentNode.children('.menu_levels'));
                if ($('#menu_'+$('#mid').val()).parent() != new_parentNode) {
                    if ($('#rank').val() > (menu_elements.length)) {
                        new_parentNode.append($('#menu_'+$('#mid').val()));
                    } else {
                        new_parentNode.insertBefore(
                            $('#menu_'+$('#mid').val()),
                            menu_elements[$('#rank').val() - 1]
                        );
                    }
                } else {
                    var oldRank = menu_elements.indexOf($('#menu_'+$('#mid').val()));
                    if ($('#rank').val() > oldRank) {
                        new_parentNode.insertBefore(
                            $('#menu_'+$('#mid').val()),
                            menu_elements[$('#rank').val() - 1].next()
                        );
                    } else {
                        new_parentNode.insertBefore(
                            $('#menu_'+$('#mid').val()),
                            menu_elements[$('#rank').val() - 1]
                        );
                    }
                }
                stopAction();
            }
            MenuAjax.showResponse(response);
        }
    }
}

function setRanksCombo(gid, pid, selected) {
    $('#rank').empty();
    if (pid == 0) {
        var new_parentNode = $('#group_'+gid);
    } else {
        var new_parentNode = $('#menu_'+pid);
    }
    var rank = new_parentNode.children('.menu_levels').length;

    if (($('#mid').val() < 1) || ($('#menu_'+$('#mid').val()).parent() != new_parentNode)) {
        rank = rank + 1;
    }

    for(var i = 0; i < rank; i++) {
        $('#rank').append($('<option>').val(i+1).text(i+1));
    }
    if (selected == null) {
        $('#rank').val(rank);
    } else {
        $('#rank').val(selected);
    }
}

/**
 * Add group
 */
function addGroup()
{
    if (cacheGroupForm == null) {
        cacheGroupForm = MenuAjax.callSync('GetGroupUI');
    }
    currentAction = 'Groups';

    $('#edit_area span').first().html(addGroupTitle);
    selectedGroup = null;
    $('#btn_cancel').css('display', 'inline');
    $('#btn_del').css('display', 'none');
    $('#btn_save').css('display', 'inline');
    $('#btn_add').css('display', 'none');
    $('#menus_edit').html(cacheGroupForm);
}

/**
 */
function mm_enter(eid)
{
    m_bg_color = $(eid).css('background-color');
    if ($(eid).parent().attr('class') != 'menu_groups') {
        $(eid).css('background-color', "#f0f0f0");
    }
    $(eid).children('div').first().next().css('visibility', 'visible');
}

/**
 */
function mm_leave(eid)
{
    $(eid).css('background-color', m_bg_color);
    if ($(eid).parent().attr('class') != 'menu_groups') {
        $(eid).children('div').first().next().css('visibility', 'hidden');
    }
}

/**
 * Add menu
 */
function addMenu(gid, pid)
{
    if (cacheMenuForm == null) {
        cacheMenuForm = MenuAjax.callSync('GetMenuUI');
    }

    stopAction();
    currentAction = 'Menus';

    if (pid == 0) {
        $('#edit_area').find('span').html(
            addMenuTitle + ' - ' + $('#group_'+gid).find('a').html()
        );
    } else {
        $('#edit_area').find('span').html(
            addMenuTitle + ' - ' + $('#group_'+gid).find('a').html() +
            ' - ' + $('#menu_'+pid).find('a').html());
    }

    selectedMenu = null;
    $('#btn_cancel').css('display', 'inline');
    $('#btn_del').css('display', 'none');
    $('#btn_save').css('display', 'inline');
    $('#btn_add').css('display', 'none');
    $('#menus_edit').html(cacheMenuForm);

    $('#gid').val(gid);
    getParentMenus(gid, 0);
    $('#pid').val(pid);
    setRanksCombo(gid, pid);

    getReferences($('#type').val());
    $('#references').prop('selectedIndex', -1);
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    if (cacheGroupForm == null) {
        cacheGroupForm = MenuAjax.callSync('GetGroupUI');
    }
    currentAction = 'Groups';
    selectedGroup = gid;

    $('#edit_area span').first().html(editGroupTitle + ' - ' + $('#group_'+gid + ' a').first().html());
    $('#btn_cancel').css('display', 'inline');
    $('#btn_del').css('display', 'inline');
    $('#btn_save').css('display', 'inline');
    $('#btn_add').css('display', 'none');
    $('#menus_edit').html(cacheGroupForm);  

    var groupInfo = MenuAjax.callSync('GetGroups', selectedGroup);

    $('#gid').val(groupInfo['id']);
    $('#title').val(groupInfo['title'].defilter());
    $('#title_view').val(groupInfo['title_view']);
    $('#published').val(Number(groupInfo['published']));
}

/**
 * Edit menu
 */
function editMenu(mid)
{
    if (mid == 0) return;
    if (cacheMenuForm == null) {
        cacheMenuForm = MenuAjax.callSync('GetMenuUI');
    }
    currentAction = 'Menus';

    $('#edit_area span').first().html(editMenuTitle + ' - ' + $('#menu_'+mid + ' a').first().html());
    $('#btn_cancel').css('display', 'inline');
    $('#btn_del').css('display', 'inline');
    $('#btn_save').css('display', 'inline');
    $('#btn_add').css('display', 'none');
    $('#menus_edit').html(cacheMenuForm);  

    //highlight selected menu
    if (selectedMenu != mid) {
        org_m_bg_color = m_bg_color;
        m_bg_color = '#eeeecc';
        if (selectedMenu != null) {
            $('#menu_'+selectedMenu).children('div').first().css('background-color', org_m_bg_color);
        }
    }
    $('#menu_'+mid).children('div').first().css('background-color', m_bg_color);

    selectedMenu = mid;
    var menuInfo = MenuAjax.callSync('GetMenu', selectedMenu);
    getParentMenus(menuInfo['gid'], mid);

    $('#mid').val(menuInfo['id']);
    $('#pid').val(menuInfo['pid']);
    $('#gid').val(menuInfo['gid']);
    $('#type').val(menuInfo['menu_type']);
    $('#title').val(menuInfo['title'].defilter());
    $('#url').val(decodeURI(menuInfo['url']));
    $('#url_target').val(menuInfo['url_target']);
    aclInfo = menuInfo['acl_key_name'] + ':' + menuInfo['acl_key_subkey'];

    setRanksCombo($('#gid').val(), $('#pid').val());
    $('#rank').val(menuInfo['rank']);

    $('#published').val(Number(menuInfo['published']));
    getReferences($('#type').val());
    $('#references').val(menuInfo['url']);
    if ($('#type').val() == 'url' && $('#references').prop('selectedIndex') == -1) {
        $('#references').prop('selectedIndex', 0);
    }

    $('#imagename').val('true');
    if (!menuInfo['image']) {
        $('#image').attr('src', 'gadgets/Menu/Resources/images/no-image.png?' + $.now());
    } else {
        $('#image').attr('src', base_script + '?gadget=Menu&action=LoadImage&id=' + menuInfo['id'] + '&' + $.now());
    }
}

/**
 * Delete group/menu
 */
function delMenus()
{
    if (currentAction == 'Groups') {
        var gid = selectedGroup;
        var msg = confirmGroupDelete;
        msg = msg.substr(0,  msg.indexOf('%s%')) + $('#group_'+gid).find('a').html() + msg.substr(msg.indexOf('%s%')+3);
        if (confirm(msg)) {
            cacheMenuForm = null;
            var response = MenuAjax.callSync('DeleteGroup', gid);
            if (response[0]['type'] == 'response_notice') {
                $('#group_' + gid).remove();
            }
            stopAction();
            MenuAjax.showResponse(response);
        }
    } else {
        var mid = selectedMenu;
        var msg = confirmMenuDelete;
        msg = msg.substr(0,  msg.indexOf('%s%')) + $('#menu_'+mid).find('a').html() + msg.substr(msg.indexOf('%s%')+3);
        if (confirm(msg)) {
            var response = MenuAjax.callSync('DeleteMenu', mid);
            if (response[0]['type'] == 'response_notice') {
                $('#menu_' + mid).remove();
            }
            stopAction();
            MenuAjax.showResponse(response);
        }
    }
}

/**
 * Get list of menu levels
 */
function getParentMenus(gid, mid) {
    var parents = MenuAjax.callSync('GetParentMenus', [gid, mid]);
    $('#pid').empty();
    $.each(parents, function(key, item) {
        $('#pid').append($('<option>').val(item['pid']).text(item['title']));
    });
}

/**
 * Get a list of public URLs
 */
function changeType(type) {
    getReferences(type);
    $('#references').prop('selectedIndex', -1);
}

/**
 * Get a list of public URLs
 */
function getReferences(type)
{
    if (cacheReferences[type]) {
        $('#references').empty();
        for(var i = 0; i < cacheReferences[type].length; i++) {
            $('#references').append($('<option>').val(cacheReferences[type][i]['url']).text(cacheReferences[type][i]['title']));
        }
        return;
    }
    var links = MenuAjax.callSync('GetPublicURList', type);
    cacheReferences[type] = new Array();
    $('#references').empty();
    $.each(links, function(i, link) {
        $('#references').append($('<option>').val(link['url']).text(link['title']));
        cacheReferences[type][i] = new Array();
        cacheReferences[type][i]['url']   = link['url'];
        cacheReferences[type][i]['title'] = link['title'];
        if (link['title2']) {
            cacheReferences[type][i]['title2'] = link['title2'];
        }
        cacheReferences[type][i]['acl_key'] = link['acl_key'];
        cacheReferences[type][i]['acl_subkey'] = link['acl_subkey'];
    });
}

/**
 * change references
 */
function changeReferences() {
    var type = $('#type').val();
    var selIndex = $('#references').prop('selectedIndex');
    if (type != 'url') {
        if (cacheReferences[type][selIndex]['title2']) {
            $('#title').val(cacheReferences[type][selIndex]['title2']);
        } else {
            $('#title').val($('#references option').eq(selIndex).text());
        }
        if (cacheReferences[type][selIndex]['acl_key']) {
            aclInfo = cacheReferences[type][selIndex]['acl_key'] + ":" + cacheReferences[type][selIndex]['acl_subkey'];
        }
    }

    if ($('#references').val() != '') {
        $('#url').val(decodeURI($('#references').val()));
    }
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    $('#btn_cancel').css('display', 'none');
    $('#btn_del').css('display', 'none');
    $('#btn_save').css('display', 'none');
    $('#btn_add').css('display', 'inline');

    var old_selected_menu = $('#menu_'+selectedMenu);
    if (selectedMenu && old_selected_menu) {
        old_selected_menu.find('div').css('background-color', org_m_bg_color);
    }

    selectedMenu  = null;
    selectedGroup = null;
    aclInfo = null;
    currentAction = null;
    $('#menus_edit').html('');
    $('#edit_area span').first().html('');
}

/**
 * Uploads the image
 */
function upload() {
    showWorkingNotification();
    $('#menus_edit').append($('<iframe></iframe>').attr({'id': 'ifrm_upload', 'name':'ifrm_upload'}));
    $('#frm_image').submit();
}

/**
 * Loads and sets the uploaded image
 */
function onUpload(response) {
    hideWorkingNotification();
    if (response.type === 'error') {
        alert(response.message);
        $('#frm_image')[0].reset();
    } else {
        var filename = encodeURIComponent(response.message) + '&' + $.now();
        $('#image').attr('src', MenuAjax.baseScript + '?gadget=Menu&action=LoadImage&file=' + filename);
        $('#imagename').val(response.message);
    }
    $('#ifrm_upload').remove();
}

/**
 * Removes the image
 */
function removeImage() {
    $('#imagename').val('');
    $('#frm_image')[0].reset();
    $('#image').attr('src', 'gadgets/Menu/Resources/images/no-image.png?' + $.now());
}

var MenuAjax = new JawsAjax('Menu', MenuCallback);

//Current group
var selectedGroup = null;

//Current menu
var selectedMenu = null;

//Cache for saving the group form template
var cacheGroupForm = null;

//Cache for saving the menu form template
var cacheMenuForm = null;

//Menu items background color
var m_bg_color = null;
var org_m_bg_color = null;

var cacheReferences = new Array();

var aclInfo = null;