/**
 * Menu JS actions
 *
 * @category    Ajax
 * @package     Menu
 */
/**
 * Use async mode, create Callback
 */
var MenuCallback = {

    UpdateGroup: function(response) {
        if (response[0]['type'] == 'alert-success') {
            $('#group_'+$('#gid').val()).find('a').first().html($('#title').val());
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
    setOrderCombo($('#gid').val(), pid);
}

function AddNewMenuGroup(gid) {
    $('#menus_trees').append(
        $('<div>').attr({'id': "group_"+gid, 'class': 'menu_groups'}).append(
            $('#group_1').find('div').first().clone(true)
        )
    );
    $("#group_"+gid).find('a')
        .first().attr('href', 'javascript: editGroup('+gid+');').html($('#title').val())
        .next().attr('href', 'javascript: addMenu('+gid+', 0);');
}

/**
 *
 */
function AddNewMenuItem(gid, pid, mid, order)
{
    if (pid == 0) {
        var parentNode = $('#group_'+gid);
    } else {
        var parentNode = $('#menu_'+pid);
    }

    var mainDiv = $('<div>').attr({'id': "menu_"+mid, 'class': 'menu_levels'}).append(
        parentNode.find('div').first().clone(true).css('background-color', '#f7f7f7')
    );

    //set order
    var menu_elements = parentNode.children('.menu_levels');
    var oldOrder = parentNode.children('.menu_levels').length;
    if (order < oldOrder) {
        mainDiv.insertBefore(menu_elements.eq(order - 1));
    } else {
        parentNode.append(mainDiv);
    }

    $("#menu_" + mid + ' .title').find('img').attr('src', 'gadgets/Menu/Resources/images/menu-item.png');
    $("#menu_" + mid + ' .title').find('a').attr('href', 'javascript: editMenu(' + mid + ');').text($('#title').val());
    $("#menu_" + mid + ' .menu_actions').find('a').attr('href', 'javascript: addMenu(' + gid + ', ' + mid + ');');
    // // hide menu actions
    $("#menu_" + mid).find('div').eq(2).css('visibility', 'hidden');
}

/**
 * Saves data / changes
 */
function saveMenus()
{
    if (currentAction == 'Groups') {
        if (!$('#title').val()) {
            alert(jaws.Menu.Defines.incompleteFields);
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
            if (response[0]['type'] == 'alert-success') {
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
            alert(jaws.Menu.Defines.incompleteFields);
            return false;
        }
        if (selectedMenu == null) {
            var response = MenuAjax.callSync(
                'InsertMenu', [
                    $('#pid').val(),
                    $('#gid').val(),
                    $('#type').val(),
                    $('#permission').val(),
                    $('#title').val(),
                    $('#url').val(),
                    $('#variables').val(),
                    $('#target').val(),
                    $('#order').val(),
                    $('#status').val(),
                    $('#imagename').val()
                ]
            );
            if (response[0]['type'] == 'alert-success') {
                var mid = response[0]['text'].substr(0, response[0]['text'].indexOf('%%'));
                response[0]['text'] = response[0]['text'].substr(response[0]['text'].indexOf('%%')+2);
                AddNewMenuItem($('#gid').val(), $('#pid').val(), mid, $('#order').val());
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
                    $('#permission').val(),
                    $('#title').val(),
                    $('#url').val(),
                    $('#variables').val(),
                    $('#target').val(),
                    $('#order').val(),
                    $('#status').val(),
                    $('#imagename').val()
                ]
            );
            if (response[0]['type'] == 'alert-success') {
                $('#menu_'+$('#mid').val()).find('a').first().html($('#title').val());
                if ($('#pid').val() == 0) {
                    var new_parentNode = $('#group_'+$('#gid').val());
                } else {
                    var new_parentNode = $('#menu_'+$('#pid').val());
                }

                var menu_elements = new_parentNode.children('.menu_levels');
                if ($('#menu_'+$('#mid').val()).parent().is(new_parentNode)) {
                    var oldOrder = menu_elements.index($('#menu_'+$('#mid').val())) + 1;
                    if ($('#order').val() == menu_elements.length) {
                        $('#menu_'+$('#mid').val()).insertAfter(menu_elements.eq($('#order').val()-1));
                    } else {
                        $('#menu_'+$('#mid').val()).insertBefore(menu_elements.eq($('#order').val()-1));
                    }
                } else {
                    if ($('#order').val() > (menu_elements.length)) {
                        new_parentNode.append($('#menu_'+$('#mid').val()));
                    } else {
                        $('#menu_'+$('#mid').val()).insertBefore(menu_elements.eq($('#order').val()-1));
                    }
                }
                stopAction();
            }
            MenuAjax.showResponse(response);
        }
    }
}

function setOrderCombo(gid, pid, selected) {
    $('#order').empty();
    if (pid == 0) {
        var new_parentNode = $('#group_'+gid);
    } else {
        var new_parentNode = $('#menu_'+pid);
    }
    var order = new_parentNode.children('.menu_levels').length;

    if (($('#mid').val() < 1) || !$('#menu_'+$('#mid').val()).parent().is(new_parentNode)) {
        order = order + 1;
    }

    for(var i = 0; i < order; i++) {
        $('#order').append($('<option>').val(i+1).text(i+1));
    }
    if (selected == null) {
        $('#order').val(order);
    } else {
        $('#order').val(selected);
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

    $('#edit_area span').first().html(jaws.Menu.Defines.addGroupTitle);
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
    $(eid).find('div').first().next().css('visibility', 'visible');
}

/**
 */
function mm_leave(eid)
{
    $(eid).css('background-color', m_bg_color);
    if ($(eid).parent().attr('class') != 'menu_groups') {
        $(eid).find('div').first().next().css('visibility', 'hidden');
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
        $('#edit_area').find('span').first().html(
            jaws.Menu.Defines.addMenuTitle + ' - ' + $('#group_'+gid).find('a').first().html()
        );
    } else {
        $('#edit_area').find('span').first().html(
            jaws.Menu.Defines.addMenuTitle + ' - ' + $('#group_'+gid).find('a').first().html() +
            ' - ' + $('#menu_'+pid).find('a').first().html()
        );
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
    setOrderCombo(gid, pid);

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

    $('#edit_area span').first().html(jaws.Menu.Defines.editGroupTitle + ' - ' + $('#group_'+gid + ' a').first().html());
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

    $('#edit_area span').first().html(jaws.Menu.Defines.editMenuTitle + ' - ' + $('#menu_'+mid + ' a').first().html());
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
            $('#menu_'+selectedMenu).find('div').first().css('background-color', org_m_bg_color);
        }
    }
    $('#menu_'+mid).find('div').first().css('background-color', m_bg_color);

    selectedMenu = mid;
    var menuInfo = MenuAjax.callSync('GetMenu', selectedMenu);
    getParentMenus(menuInfo['gid'], mid);

    $('#mid').val(menuInfo['id']);
    $('#pid').val(menuInfo['pid']);
    $('#gid').val(menuInfo['gid']);
    $('#type').val(menuInfo['type']);
    $('#title').val(menuInfo['title'].defilter());
    $('#url').val(menuInfo['url']);
    $('#url').prop('disabled', menuInfo['variables'] || !menuInfo['url']);
    $('#variables').val(menuInfo['variables']);
    $('#target').val(menuInfo['target']);
    $('#permission').val(menuInfo['permission']);
    setOrderCombo($('#gid').val(), $('#pid').val());
    $('#order').val(menuInfo['order']);
    $('#status').val(menuInfo['status']);
    getReferences($('#type').val());
    $('#references').val(menuInfo['url']);
    if ($('#type').val() == 'url' && $('#references').prop('selectedIndex') == -1) {
        $('#references').prop('selectedIndex', 0);
    }

    $('#imagename').val('true');
    if (!menuInfo['image']) {
        $('#image').attr('src', 'gadgets/Menu/Resources/images/no-image.png?' + $.now());
    } else {
        $('#image').attr('src', jaws.Menu.Defines.base_script + '?gadget=Menu&action=LoadImage&id=' + menuInfo['id'] + '&' + $.now());
    }
}

/**
 * Delete group/menu
 */
function delMenus()
{
    if (currentAction == 'Groups') {
        var gid = selectedGroup;
        var msg = jaws.Menu.Defines.confirmGroupDelete;
        msg = msg.substr(0, msg.indexOf('%s%')) +
              $('#group_'+gid).find('a').first().html() +
              msg.substr(msg.indexOf('%s%') + 3);
        if (confirm(msg)) {
            cacheMenuForm = null;
            var response = MenuAjax.callSync('DeleteGroup', gid);
            if (response[0]['type'] == 'alert-success') {
                $('#group_'+gid).remove();
            }
            stopAction();
            MenuAjax.showResponse(response);
        }
    } else {
        var mid = selectedMenu;
        var msg = jaws.Menu.Defines.confirmMenuDelete;
        msg = msg.substr(0,  msg.indexOf('%s%')) + $('#menu_'+mid).find('a').first().html() + msg.substr(msg.indexOf('%s%')+3);
        if (confirm(msg)) {
            var response = MenuAjax.callSync('DeleteMenu', mid);
            if (response[0]['type'] == 'alert-success') {
                $('#menu_'+mid).remove();
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
    if (cachedMenus[type]) {
        $('#references').empty();
        for(var i = 0; i < cachedMenus[type].length; i++) {
            $('#references').append($('<option>').val(cachedMenus[type][i]['url']).text(cachedMenus[type][i]['title']));
        }
        return;
    }
    var links = MenuAjax.callSync('GetPublicURList', type);
    cachedMenus[type] = new Array();
    $('#references').empty();
    $.each(links, function(i, link) {
        $('#references').append($('<option>').val(link['url']).text(link['title']));
        cachedMenus[type][i] = new Array();
        cachedMenus[type][i]['url']   = link['url'];
        cachedMenus[type][i]['variables'] = link['variables']? link['variables'] : '';
        cachedMenus[type][i]['title'] = link['title'];
        if (link['title2']) {
            cachedMenus[type][i]['title2'] = link['title2'];
        }
        
        cachedMenus[type][i]['permission'] = link['permission']? link['permission'] : '';
        if (link['status']) {
            cachedMenus[type][i]['status'] = link['status'];
        }
    });
}

/**
 * change references
 */
function changeReferences() {
    var type = $('#type').val();
    var selIndex = $('#references').prop('selectedIndex');
    if (type != 'url') {
        if (cachedMenus[type][selIndex]['title2']) {
            $('#title').val(cachedMenus[type][selIndex]['title2']);
        } else {
            $('#title').val($("#references option").eq(selIndex).text());
        }
        if (cachedMenus[type][selIndex]['status']) {
            $('#status').val(cachedMenus[type][selIndex]['status']);
        } else {
            $('#status').val(1);
        }
    }

    $('#variables').val(cachedMenus[type][selIndex]['variables']);
    $('#permission').val(cachedMenus[type][selIndex]['permission']);
    $('#url').val($('#references').val());
    $('#url').prop('disabled', cachedMenus[type][selIndex]['variables'] || !cachedMenus[type][selIndex]['url']);
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
    if ($('#menu_'+selectedMenu).length) {
        $('#menu_'+selectedMenu).find('div').first().css('background-color', org_m_bg_color);
    }

    selectedMenu  = null;
    selectedGroup = null;
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

var cachedMenus = new Array();
