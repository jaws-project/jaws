/**
 * Menu JS actions
 *
 * @category    Ajax
 * @package     Menu
 * @author      Jonathan Hernandez <ion@gluch.org.mx>
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2005-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var MenuCallback = {

    updategroup: function(response) {
        if (response[0]['css'] == 'notice-message') {
            _('group_'+_('gid').value).getElementsByTagName('a')[0].innerHTML = _('title').value;
            stopAction();
        }
        showResponse(response);
    }
}

function isValidURL(url) {
    return (/^(((ht|f)tp(s?))\:\/\/).*$/.test(url));
}

function changeMenuGroup(gid, mid) {
    mid = ((mid == null)? _('mid').value : mid);
    getParentMenus(gid, mid);
    changeMenuParent(0);
}

function changeMenuParent(pid) {
    setRanksCombo(_('gid').value, pid);
}

function AddNewMenuGroup(gid) {
    var mainDiv = document.createElement('div');
    var div =_('group_1').getElementsByTagName('div')[0].cloneNode(true);
    mainDiv.className = 'menu_groups';
    mainDiv.id = "group_"+gid;
    mainDiv.appendChild(div);
    _('menus_trees').appendChild(mainDiv);
    var links = mainDiv.getElementsByTagName('a');
    links[0].href      = 'javascript: editGroup('+gid+');';
    links[0].innerHTML = _('title').value;
    links[1].href = 'javascript: addMenu('+gid+', 0);';
}

/**
 *
 */
function AddNewMenuItem(gid, pid, mid, rank)
{
    var mainDiv = document.createElement('div');
    var div =_('group_1').getElementsByTagName('div')[0].cloneNode(true);
    mainDiv.className = 'menu_levels';
    mainDiv.id = "menu_"+mid;
    mainDiv.appendChild(div);
    if (pid == 0) {
        var parentNode = _('group_'+gid);
    } else {
        var parentNode = _('menu_'+pid);
    }
    parentNode.appendChild(mainDiv);
    //set ranking
    var oldRank = Array.from(parentNode.childNodes).indexOf(_('menu_'+mid));
    if (rank < oldRank) {
        parentNode.insertBefore(_('menu_'+mid), parentNode.childNodes[rank]);
    }
    //--
    var links = mainDiv.getElementsByTagName('a');
    links[0].href      = 'javascript: editMenu('+mid+');';
    links[0].innerHTML = _('title').value;
    links[1].href = 'javascript: addMenu('+gid+', '+ mid +');';
    var images = mainDiv.getElementsByTagName('img');
    images[0].src = menuImageSrc;
    // hide menu actions
    mainDiv.getElementsByTagName('div')[2].style.visibility = 'hidden';
}

/**
 * Saves data / changes
 */
function saveMenus()
{
    if (currentAction == 'Groups') {
        if (_('title').value.blank()) {
            alert(incompleteFields);
            return false;
        }
        cacheMenuForm = null;
        if (selectedGroup == null) {
            var response = MenuAjax.callSync('insertgroup',
                                             _('title').value,
                                             _('title_view').value,
                                             _('published').value);
            if (response[0]['css'] == 'notice-message') {
                var gid = response[0]['data'];
                AddNewMenuGroup(gid);
                stopAction();
            }
            showResponse(response);
        } else {
            MenuAjax.callAsync('updategroup',
                               _('gid').value,
                               _('title').value,
                               _('title_view').value,
                               _('published').value);
        }
    } else {
        if (_('title').value.blank() || (_('references').selectedIndex == -1)) {
            alert(incompleteFields);
            return false;
        }
        if (selectedMenu == null) {
            var response = MenuAjax.callSync(
                                    'insertmenu',
                                    _('pid').value,
                                    _('gid').value,
                                    _('type').value,
                                    _('title').value,
                                    encodeURI(_('url').value),
                                    _('url_target').value,
                                    _('rank').value,
                                    _('published').value,
                                    _('imagename').value);
            if (response[0]['css'] == 'notice-message') {
                var mid = response[0]['message'].substr(0, response[0]['message'].indexOf('%%'));
                response[0]['message'] = response[0]['message'].substr(response[0]['message'].indexOf('%%')+2);
                AddNewMenuItem(_('gid').value, _('pid').value, mid, _('rank').value);
                stopAction();
            }
            showResponse(response);
        } else {
            var response = MenuAjax.callSync(
                                    'updatemenu',
                                    _('mid').value,
                                    _('pid').value,
                                    _('gid').value,
                                    _('type').value,
                                    _('title').value,
                                    encodeURI(_('url').value),
                                    _('url_target').value,
                                    _('rank').value,
                                    _('published').value,
                                    _('imagename').value);
            if (response[0]['css'] == 'notice-message') {
                _('menu_'+_('mid').value).getElementsByTagName('a')[0].innerHTML = _('title').value;
                if (_('pid').value == 0) {
                    var new_parentNode = _('group_'+_('gid').value);
                } else {
                    var new_parentNode = _('menu_'+_('pid').value);
                }
                if (_('menu_'+_('mid').value).parentNode != new_parentNode) {
                    if (_('rank').value > (new_parentNode.getChildren('.menu_levels').length)) {
                        new_parentNode.appendChild(_('menu_'+_('mid').value));
                    } else {
                        new_parentNode.insertBefore(_('menu_'+_('mid').value), new_parentNode.childNodes[_('rank').value]);
                    }
                } else {
                    var oldRank = Array.from(new_parentNode.getChildren()).indexOf(_('menu_'+_('mid').value));
                    if (_('rank').value > oldRank) {
                        new_parentNode.insertBefore(_('menu_'+_('mid').value), new_parentNode.childNodes[_('rank').value].nextSibling);
                    } else {
                        new_parentNode.insertBefore(_('menu_'+_('mid').value), new_parentNode.childNodes[_('rank').value]);
                    }
                }
                stopAction();
            }
            showResponse(response);
        }
    }
}

function setRanksCombo(gid, pid, selected) {
    _('rank').options.length = 0;
    if (pid == 0) {
        var new_parentNode = _('group_'+gid);
    } else {
        var new_parentNode = _('menu_'+pid);
    }
    var rank = new_parentNode.getChildren('.menu_levels').length;

    if ((_('mid').value < 1) || (_('menu_'+_('mid').value).parentNode != new_parentNode)) {
        rank = rank + 1;
    }

    for(var i = 0; i < rank; i++) {
        _('rank').options[i] = new Option(i+1, i+1);
    }
    if (selected == null) {
        _('rank').value = rank;
    } else {
        _('rank').value = selected;
    }
}

/**
 * Add group
 */
function addGroup()
{
    if (cacheGroupForm == null) {
        cacheGroupForm = MenuAjax.callSync('getgroupui');
    }
    currentAction = 'Groups';

    _('edit_area').getElementsByTagName('span')[0].innerHTML = addGroupTitle;
    selectedGroup = null;
    _('btn_cancel').style.display = 'inline';
    _('btn_del').style.display    = 'none';
    _('btn_save').style.display   = 'inline';
    _('btn_add').style.display    = 'none';
    _('menus_edit').innerHTML = cacheGroupForm;
}

/**
 */
function mm_enter(eid)
{
    m_bg_color = _(eid).style.backgroundColor;
    if (_(eid).parentNode.className != 'menu_groups') {
        _(eid).style.backgroundColor = "#f0f0f0";
    }
    _(eid).getElementsByTagName('div')[1].style.visibility = 'visible';
}

/**
 */
function mm_leave(eid)
{
    _(eid).style.backgroundColor = m_bg_color;
    if (_(eid).parentNode.className != 'menu_groups') {
        _(eid).getElementsByTagName('div')[1].style.visibility = 'hidden';
    }
}

/**
 * Add menu
 */
function addMenu(gid, pid)
{
    if (cacheMenuForm == null) {
        cacheMenuForm = MenuAjax.callSync('getmenuui');
    }

    stopAction();
    currentAction = 'Menus';

    if (pid == 0) {
        _('edit_area').getElementsByTagName('span')[0].innerHTML =
            addMenuTitle + ' - ' + _('group_'+gid).getElementsByTagName('a')[0].innerHTML;
    } else {
        _('edit_area').getElementsByTagName('span')[0].innerHTML =
            addMenuTitle + ' - ' + _('group_'+gid).getElementsByTagName('a')[0].innerHTML +
            ' - ' + _('menu_'+pid).getElementsByTagName('a')[0].innerHTML;
    }

    selectedMenu = null;
    _('btn_cancel').style.display = 'inline';
    _('btn_del').style.display    = 'none';
    _('btn_save').style.display   = 'inline';
    _('btn_add').style.display    = 'none';
    _('menus_edit').innerHTML = cacheMenuForm;

    _('gid').value = gid;
    getParentMenus(gid, 0);
    _('pid').value = pid;
    setRanksCombo(gid, pid);

    getReferences(_('type').value);
    _('references').selectedIndex = -1;
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    if (cacheGroupForm == null) {
        cacheGroupForm = MenuAjax.callSync('getgroupui');
    }
    currentAction = 'Groups';
    selectedGroup = gid;

    _('edit_area').getElementsByTagName('span')[0].innerHTML =
        editGroupTitle + ' - ' + _('group_'+gid).getElementsByTagName('a')[0].innerHTML;
    _('btn_cancel').style.display = 'inline';
    _('btn_del').style.display    = 'inline';
    _('btn_save').style.display   = 'inline';
    _('btn_add').style.display    = 'none';
    _('menus_edit').innerHTML = cacheGroupForm;  

    var groupInfo = MenuAjax.callSync('getgroups', selectedGroup);

    _('gid').value         = groupInfo['id'];
    _('title').value       = groupInfo['title'].defilter();
    _('title_view').value  = groupInfo['title_view'];
    _('published').value   = Number(groupInfo['published']);
}

/**
 * Edit menu
 */
function editMenu(mid)
{
    if (mid == 0) return;
    if (cacheMenuForm == null) {
        cacheMenuForm = MenuAjax.callSync('getmenuui');
    }
    currentAction = 'Menus';

    _('edit_area').getElementsByTagName('span')[0].innerHTML =
        editMenuTitle + ' - ' + _('menu_'+mid).getElementsByTagName('a')[0].innerHTML;
    _('btn_cancel').style.display = 'inline';
    _('btn_del').style.display    = 'inline';
    _('btn_save').style.display   = 'inline';
    _('btn_add').style.display    = 'none';
    _('menus_edit').innerHTML = cacheMenuForm;  

    //highlight selected menu
    if (selectedMenu != mid) {
        org_m_bg_color = m_bg_color;
        m_bg_color = '#eeeecc';
        if (selectedMenu != null) {
            _('menu_'+selectedMenu).getElementsByTagName('div')[0].style.backgroundColor = org_m_bg_color;
        }
    }
    _('menu_'+mid).getElementsByTagName('div')[0].style.backgroundColor = m_bg_color;

    selectedMenu = mid;
    var menuInfo = MenuAjax.callSync('getmenu', selectedMenu);
    getParentMenus(menuInfo['gid'], mid);

    _('mid').value         = menuInfo['id'];
    _('pid').value         = menuInfo['pid'];
    _('gid').value         = menuInfo['gid'];
    _('type').value        = menuInfo['menu_type'];
    _('title').value       = menuInfo['title'].defilter();
    _('url').value         = decodeURI(menuInfo['url']);
    _('url_target').value  = menuInfo['url_target'];

    setRanksCombo(_('gid').value, _('pid').value);
    _('rank').value        = menuInfo['rank'];

    _('published').value   = Number(menuInfo['published']);
    getReferences(_('type').value);
    _('references').value = menuInfo['url'];
    if (_('type').value == 'url' && _('references').selectedIndex == -1) {
        _('references').selectedIndex = 0;
    }

    _('imagename').value  = 'true';
    if (!menuInfo['image']) {
        _('image').src = 'gadgets/Menu/images/no-image.png?' + (new Date()).getTime();
    } else {
        _('image').src = base_script + '?gadget=Menu&action=LoadImage&id=' + menuInfo['id'] + '&' + (new Date()).getTime();;
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
        msg = msg.substr(0,  msg.indexOf('%s%')) + _('group_'+gid).getElementsByTagName('a')[0].innerHTML + msg.substr(msg.indexOf('%s%')+3);
        if (confirm(msg)) {
            cacheMenuForm = null;
            var response = MenuAjax.callSync('deletegroup', gid);
            if (response[0]['css'] == 'notice-message') {
                Element.destroy(_('group_'+gid));
            }
            stopAction();
            showResponse(response);
        }
    } else {
        var mid = selectedMenu;
        var msg = confirmMenuDelete;
        msg = msg.substr(0,  msg.indexOf('%s%')) + _('menu_'+mid).getElementsByTagName('a')[0].innerHTML + msg.substr(msg.indexOf('%s%')+3);
        if (confirm(msg)) {
            var response = MenuAjax.callSync('deletemenu', mid);
            if (response[0]['css'] == 'notice-message') {
                Element.destroy(_('menu_'+mid));
            }
            stopAction();
            showResponse(response);
        }
    }
}

/**
 * Get list of menu levels
 */
function getParentMenus(gid, mid) {
    var parents = MenuAjax.callSync('getparentmenus', gid, mid);
    _('pid').options.length = 0;
    for(var i = 0; i < parents.length; i++) {
        _('pid').options[i] = new Option(parents[i]['title'], parents[i]['pid']);
    }
}

/**
 * Get a list of public URLs
 */
function changeType(type) {
    getReferences(type);
    _('references').selectedIndex = -1;
}

/**
 * Get a list of public URLs
 */
function getReferences(type)
{
    if (cacheReferences[type]) {
        _('references').options.length = 0;
        for(var i = 0; i < cacheReferences[type].length; i++) {
            _('references').options[i] = new Option(cacheReferences[type][i]['title'], cacheReferences[type][i]['url']);
        }
        return;
    }
    var links = MenuAjax.callSync('getpublicurlist', type);
    cacheReferences[type] = new Array();
    _('references').options.length = 0;
    for(var i = 0; i < links.length; i++) {
        _('references').options[i] = new Option(links[i]['title'], links[i]['url']);
        cacheReferences[type][i] = new Array();
        cacheReferences[type][i]['url']   = links[i]['url'];
        cacheReferences[type][i]['title'] = links[i]['title'];
        if (links[i]['title2']) {
            cacheReferences[type][i]['title2'] = links[i]['title2'];
        }
    }
}

/**
 * change references
 */
function changeReferences() {
    var type = _('type').value;
    var selIndex = _('references').selectedIndex;
    if (type != 'url') {
        if (cacheReferences[type][selIndex]['title2']) {
            _('title').value = cacheReferences[type][selIndex]['title2'];
        } else {
            _('title').value = _('references').options[selIndex].text;
        }
    }

    if (_('references').value !='') {
        _('url').value = decodeURI(_('references').value);
    }
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    _('btn_cancel').style.display = 'none';
    _('btn_del').style.display    = 'none';
    _('btn_save').style.display   = 'none';
    _('btn_add').style.display    = 'inline';

    var old_selected_menu = _('menu_'+selectedMenu);
    if (old_selected_menu) {
        old_selected_menu.getElementsByTagName('div')[0].style.backgroundColor = org_m_bg_color;
    }

    selectedMenu  = null;
    selectedGroup = null;
    currentAction = null;
    _('menus_edit').innerHTML = '';
    _('edit_area').getElementsByTagName('span')[0].innerHTML = '';
}

/**
 * Uploads the image
 */
function upload() {
    showWorkingNotification();
    var iframe = new Element('iframe', {id:'ifrm_upload', name:'ifrm_upload'});
    _('menus_edit').adopt(iframe);
    _('frm_image').submit();
}

/**
 * Loads and sets the uploaded image
 */
function onUpload(response) {
    hideWorkingNotification();
    if (response.type === 'error') {
        alert(response.message);
        _('frm_image').reset();
    } else {
        var filename = encodeURIComponent(response.message) + '&' + (new Date()).getTime();
        _('image').src = base_script + '?gadget=Menu&action=LoadImage&file=' + filename;
        _('imagename').value = response.message;
    }
    _('ifrm_upload').destroy();
}

/**
 * Removes the image
 */
function removeImage() {
    _('imagename').value = '';
    _('frm_image').reset();
    _('image').src = 'gadgets/Menu/images/no-image.png?' + (new Date()).getTime();
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
