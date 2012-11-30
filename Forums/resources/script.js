/**
 * Forums JS actions
 *
 * @category   Ajax
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var ForumsCallback = {

    updategroup: function(response) {
        if (response['css'] == 'notice-message') {
            $('group_'+$('gid').value).getElementsByTagName('a')[0].innerHTML = $('title').value;
            stopAction();
        }
        showResponse(response);
    }
}

/**
 * Select Tree row
 *
 */
function selectTreeRow(rowElement)
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRowColor = rowElement.style.backgroundColor;
    rowElement.style.backgroundColor = '#eec';
    selectedRow = rowElement;
}

/**
 * Unselect Tree row
 *
 */
function unselectTreeRow()
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRow = null;
    selectedRowColor = null;
}

/**
 *
 */
function AddNewForumGroup(gid) {
    var mainDiv = document.createElement('div');
    var div =$('group_1').getElementsByTagName('div')[0].cloneNode(true);
    mainDiv.className = 'forums_group_area';
    mainDiv.id = "group_"+gid;
    mainDiv.appendChild(div);
    $('forums_tree').appendChild(mainDiv);
    var links = mainDiv.getElementsByTagName('a');
    links[0].href      = 'javascript:editGroup('+gid+');';
    links[0].innerHTML = $('title').value;
    links[1].href = 'javascript:addForum('+gid+');';
}

/**
 *
 */
function AddNewForumItem(gid, fid, order)
{
    var mainDiv = document.createElement('div');
    mainDiv.className = 'forums_group_item';
    mainDiv.id = 'forum_'+fid;
    var parentNode = $('group_'+gid);
    parentNode.appendChild(mainDiv);
    // image
    var image = document.createElement('img');
    image.className = 'icon';
    image.src = forumImageSrc;
    mainDiv.appendChild(image);
    // link
    var link  = document.createElement('a');
    link.setAttribute('onclick', 'javascript:editForum(this, '+fid+');');
    link.innerHTML = $('title').value;
    mainDiv.appendChild(link);
}

/**
 * Saves data / changes
 */
function saveForums()
{
    if ((jawsTrim($('title').value) == '')) {
        alert(incompleteFields);
        return false;
    }

    if (currentAction == 'Groups') {
        cacheForumForm = null;
        if ($('gid').value == 0) {
            var response = forumsSync.insertgroup(
                                    $('title').value,
                                    $('description').value,
                                    $('fast_url').value,
                                    $('order').value,
                                    $('locked').value,
                                    $('published').value);
            if (response['css'] == 'notice-message') {
                AddNewForumGroup(response['data']);
                stopAction();
            }
            showResponse(response);
        } else {
            forumsAsync.updategroup(
                                $('gid').value,
                                $('title').value,
                                $('description').value,
                                $('fast_url').value,
                                $('order').value,
                                $('locked').value,
                                $('published').value);
        }
    } else {
        if ($('fid').value == 0) {
            var response = forumsSync.insertforum(
                                    $('gid').value,
                                    $('title').value,
                                    $('description').value,
                                    $('fast_url').value,
                                    $('order').value,
                                    $('locked').value,
                                    $('published').value);
            if (response['css'] == 'notice-message') {
                AddNewForumItem($('gid').value, response['data'], $('order').value);
                stopAction();
            }
            showResponse(response);
        } else {
            var response = forumsSync.updateforum(
                                    $('fid').value,
                                    $('gid').value,
                                    $('title').value,
                                    $('description').value,
                                    $('fast_url').value,
                                    $('order').value,
                                    $('locked').value,
                                    $('published').value);
            if (response['css'] == 'notice-message') {
                $('forum_'+$('fid').value).getElementsByTagName('a')[0].innerHTML = $('title').value;
                var new_parentNode = $('group_'+$('gid').value);
                if ($('forum_'+$('fid').value).parentNode != new_parentNode) {
                    new_parentNode.appendChild($('forum_'+$('fid').value));
                }
                stopAction();
            }
            showResponse(response);
        }
    }
}

/**
 *
 */
function groupsOrders(selected) {
    $('order').options.length = 0;
    var order = $('forums_tree').select('div.forums_group_area').length;
    order = order + ((selected == null)? 1 : 0);
    for(var i = 0; i < order; i++) {
        $('order').options[i] = new Option(i+1, i+1);
    }

    $('order').value = (selected == null)? order : selected;
}

/**
 *
 */
function forumsOrders(gid, selected) {
    $('order').options.length = 0;
    var order = $('group_'+gid).select('div.forums_group_item').length;
    order = order + ((selected == null)? 1 : 0);
    for(var i = 0; i < order; i++) {
        $('order').options[i] = new Option(i+1, i+1);
    }

    $('order').value = (selected == null)? order : selected;
}

/**
 * Add group
 */
function addGroup()
{
    if (cacheGroupForm == null) {
        cacheGroupForm = forumsSync.getgroupui();
    }
    currentAction = 'Groups';

    $('edit_area').getElementsByTagName('span').innerHTML = addGroupTitle;
    $('btn_cancel').style.display = 'inline';
    $('btn_del').style.display    = 'none';
    $('btn_save').style.display   = 'inline';
    $('btn_add').style.display    = 'none';
    $('forums_edit').innerHTML = cacheGroupForm;
    groupsOrders();
}

/**
 * Add forum
 */
function addForum(gid)
{
    if (cacheForumForm == null) {
        cacheForumForm = forumsSync.getforumui();
    }

    stopAction();
    currentAction = 'Forums';
    $('edit_area').getElementsByTagName('span').innerHTML =
        addForumTitle + ' - ' + $('group_'+gid).getElementsByTagName('a').innerHTML;

    $('btn_cancel').style.display = 'inline';
    $('btn_del').style.display    = 'none';
    $('btn_save').style.display   = 'inline';
    $('btn_add').style.display    = 'none';
    $('forums_edit').innerHTML = cacheForumForm;
    $('gid').value = gid;

    forumsOrders($('gid').value);
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    if (cacheGroupForm == null) {
        cacheGroupForm = forumsSync.getgroupui();
    }
    currentAction = 'Groups';

    $('edit_area').getElementsByTagName('span').innerHTML =
        editGroupTitle + ' - ' + $('group_'+gid).getElementsByTagName('a').innerHTML;
    $('btn_cancel').style.display = 'inline';
    $('btn_del').style.display    = 'inline';
    $('btn_save').style.display   = 'inline';
    $('btn_add').style.display    = 'none';
    $('forums_edit').innerHTML = cacheGroupForm;  

    var group = forumsSync.getgroup(gid);

    $('gid').value         = group['id'];
    $('title').value       = group['title'];
    $('description').value = group['description'];
    $('fast_url').value    = group['fast_url'];
    $('locked').value      = Number(group['locked']);
    $('published').value   = Number(group['published']);
    groupsOrders(group['order'])
}

/**
 * Edit forum
 */
function editForum(element, fid)
{
    if (fid == 0) return;
    selectTreeRow(element.parentNode);
    if (cacheForumForm == null) {
        cacheForumForm = forumsSync.getforumui();
    }
    currentAction = 'Forums';

    $('edit_area').getElementsByTagName('span').innerHTML =
        editForumTitle + ' - ' + $('forum_'+fid).getElementsByTagName('a').innerHTML;
    $('btn_cancel').style.display = 'inline';
    $('btn_del').style.display    = 'inline';
    $('btn_save').style.display   = 'inline';
    $('btn_add').style.display    = 'none';
    $('forums_edit').innerHTML = cacheForumForm;  

    var forum = forumsSync.getforum(fid);
    $('fid').value         = forum['id'];
    $('gid').value         = forum['gid'];
    $('title').value       = forum['title'];
    $('description').value = forum['description'];
    $('fast_url').value    = forum['fast_url'];
    $('locked').value      = Number(forum['locked']);
    $('published').value   = Number(forum['published']);
    forumsOrders(forum['gid'], forum['order']);
}

/**
 * Delete group/forum
 */
function delForums()
{
    if (currentAction == 'Groups') {
        var gid = $('gid').value;
        var msg = confirmGroupDelete;
        msg = msg.substr(0,  msg.indexOf('%s%')) + $('group_'+gid).getElementsByTagName('a').innerHTML + msg.substr(msg.indexOf('%s%')+3);
        if (confirm(msg)) {
            cacheForumForm = null;
            var response = forumsSync.deletegroup(gid);
            if (response['css'] == 'notice-message') {
                Element.remove($('group_'+gid));
            }
            stopAction();
            showResponse(response);
        }
    } else {
        var fid = $('fid').value;
        var msg = confirmForumDelete;
        msg = msg.substr(0,  msg.indexOf('%s%')) + $('forum_'+fid).getElementsByTagName('a').innerHTML + msg.substr(msg.indexOf('%s%')+3);
        if (confirm(msg)) {
            var response = forumsSync.deleteforum(fid);
            if (response['css'] == 'notice-message') {
                Element.remove($('forum_'+fid));
            }
            stopAction();
            showResponse(response);
        }
    }
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    $('btn_cancel').style.display = 'none';
    $('btn_del').style.display    = 'none';
    $('btn_save').style.display   = 'none';
    $('btn_add').style.display    = 'inline';

    switch(currentAction) {
        case 'Forums':
            $('fid').value = 0;
            break;
        case 'Groups':
            $('gid').value = 0;
            break;
    }

    currentAction = null;
    unselectTreeRow();
    $('forums_edit').innerHTML = '';
    $('edit_area').getElementsByTagName('span')[0].innerHTML = '';
}

var forumsAsync = new forumsadminajax(ForumsCallback);
forumsAsync.serverErrorFunc = Jaws_Ajax_ServerError;
forumsAsync.onInit = showWorkingNotification;
forumsAsync.onComplete = hideWorkingNotification;

var forumsSync  = new forumsadminajax();
forumsSync.serverErrorFunc = Jaws_Ajax_ServerError;
forumsSync.onInit = showWorkingNotification;
forumsSync.onComplete = hideWorkingNotification;

currentAction = null;

//Cache for saving the group form template
var cacheGroupForm = null;

//Cache for saving the forum form template
var cacheForumForm = null;

//Forum items background color
var m_bg_color = null;
var org_m_bg_color = null;

//Which row selected in Tree
var selectedRow = null;
var selectedRowColor = null;
