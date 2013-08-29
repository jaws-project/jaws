/**
 * Forums JS actions
 *
 * @category   Ajax
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var ForumsCallback = {

    updategroup: function(response) {
        if (response['css'] == 'notice-message') {
            _('group_'+_('gid').value).getElementsByTagName('a')[0].innerHTML = _('title').value;
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
    var div =_('group_1').getElementsByTagName('div')[0].cloneNode(true);
    mainDiv.className = 'forums_group_area';
    mainDiv.id = "group_"+gid;
    mainDiv.appendChild(div);
    _('forums_tree').appendChild(mainDiv);
    var links = mainDiv.getElementsByTagName('a');
    links[0].href      = 'javascript:editGroup('+gid+');';
    links[0].innerHTML = _('title').value;
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
    var parentNode = _('group_'+gid);
    parentNode.appendChild(mainDiv);
    // image
    var image = document.createElement('img');
    image.className = 'icon';
    image.src = forumImageSrc;
    mainDiv.appendChild(image);
    // link
    var link  = document.createElement('a');
    link.setAttribute('onclick', 'javascript:editForum(this, '+fid+');');
    link.innerHTML = _('title').value;
    mainDiv.appendChild(link);
}

/**
 * Saves data / changes
 */
function saveForums()
{
    if (_('title').value.trim() == '') {
        alert(incompleteFields);
        return false;
    }

    if (currentAction == 'Groups') {
        cacheForumForm = null;
        if (_('gid').value == 0) {
            var response = ForumsAjax.callSync('insertgroup',
                                    _('title').value,
                                    _('description').value,
                                    _('fast_url').value,
                                    _('order').value,
                                    _('locked').value,
                                    _('published').value);
            if (response['css'] == 'notice-message') {
                AddNewForumGroup(response['data']);
                stopAction();
            }
            showResponse(response);
        } else {
            ForumsAjax.callAsync('updategroup',
                                _('gid').value,
                                _('title').value,
                                _('description').value,
                                _('fast_url').value,
                                _('order').value,
                                _('locked').value,
                                _('published').value);
        }
    } else {
        if (_('fid').value == 0) {
            var response = ForumsAjax.callSync('insertforum',
                                    _('gid').value,
                                    _('title').value,
                                    _('description').value,
                                    _('fast_url').value,
                                    _('order').value,
                                    _('locked').value,
                                    _('published').value);
            if (response['css'] == 'notice-message') {
                AddNewForumItem(_('gid').value, response['data'], _('order').value);
                stopAction();
            }
            showResponse(response);
        } else {
            var response = ForumsAjax.callSync('updateforum',
                                    _('fid').value,
                                    _('gid').value,
                                    _('title').value,
                                    _('description').value,
                                    _('fast_url').value,
                                    _('order').value,
                                    _('locked').value,
                                    _('published').value);
            if (response['css'] == 'notice-message') {
                _('forum_'+_('fid').value).getElementsByTagName('a')[0].innerHTML = _('title').value;
                var new_parentNode = _('group_'+_('gid').value);
                if (_('forum_'+_('fid').value).parentNode != new_parentNode) {
                    new_parentNode.appendChild(_('forum_'+_('fid').value));
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
    _('order').options.length = 0;
    var order = _('forums_tree').getElements('div.forums_group_area').length;
    order = order + ((selected == null)? 1 : 0);
    for(var i = 0; i < order; i++) {
        _('order').options[i] = new Option(i+1, i+1);
    }

    _('order').value = (selected == null)? order : selected;
}

/**
 *
 */
function forumsOrders(gid, selected) {
    _('order').options.length = 0;
    var order = _('group_'+gid).getElements('div.forums_group_item').length;
    order = order + ((selected == null)? 1 : 0);
    for(var i = 0; i < order; i++) {
        _('order').options[i] = new Option(i+1, i+1);
    }

    _('order').value = (selected == null)? order : selected;
}

/**
 * Add group
 */
function addGroup()
{
    if (cacheGroupForm == null) {
        cacheGroupForm = ForumsAjax.callSync('getgroupui');
    }
    currentAction = 'Groups';

    _('work_area_title').innerHTML = addGroupTitle;
    _('btn_cancel').style.display = 'inline';
    _('btn_del').style.display    = 'none';
    _('btn_save').style.display   = 'inline';
    _('btn_add').style.display    = 'none';
    _('forums_edit').innerHTML = cacheGroupForm;
    groupsOrders();
}

/**
 * Add forum
 */
function addForum(gid)
{
    if (cacheForumForm == null) {
        cacheForumForm = ForumsAjax.callSync('getforumui');
    }

    stopAction();
    currentAction = 'Forums';
    _('work_area_title').innerHTML = addForumTitle + ' - ' + _('group_'+gid).getElementsByTagName('a')[0].innerHTML;

    _('btn_cancel').style.display = 'inline';
    _('btn_del').style.display    = 'none';
    _('btn_save').style.display   = 'inline';
    _('btn_add').style.display    = 'none';
    _('forums_edit').innerHTML = cacheForumForm;
    _('gid').value = gid;

    forumsOrders(_('gid').value);
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    if (cacheGroupForm == null) {
        cacheGroupForm = ForumsAjax.callSync('getgroupui');
    }
    currentAction = 'Groups';

    _('work_area_title').innerHTML = editGroupTitle + ' - ' + _('group_'+gid).getElementsByTagName('a')[0].innerHTML;
    _('btn_cancel').style.display = 'inline';
    _('btn_del').style.display    = 'inline';
    _('btn_save').style.display   = 'inline';
    _('btn_add').style.display    = 'none';
    _('forums_edit').innerHTML = cacheGroupForm;  

    var group = ForumsAjax.callSync('getgroup', gid);

    _('gid').value         = group['id'];
    _('title').value       = group['title'];
    _('description').value = group['description'];
    _('fast_url').value    = group['fast_url'];
    _('locked').value      = Number(group['locked']);
    _('published').value   = Number(group['published']);
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
        cacheForumForm = ForumsAjax.callSync('getforumui');
    }
    currentAction = 'Forums';

    _('work_area_title').innerHTML = editForumTitle + ' - ' + _('forum_'+fid).getElementsByTagName('a')[0].innerHTML;
    _('btn_cancel').style.display = 'inline';
    _('btn_del').style.display    = 'inline';
    _('btn_save').style.display   = 'inline';
    _('btn_add').style.display    = 'none';
    _('forums_edit').innerHTML = cacheForumForm;  

    var forum = ForumsAjax.callSync('getforum', fid);
    _('fid').value         = forum['id'];
    _('gid').value         = forum['gid'];
    _('title').value       = forum['title'];
    _('description').value = forum['description'];
    _('fast_url').value    = forum['fast_url'];
    _('locked').value      = Number(forum['locked']);
    _('published').value   = Number(forum['published']);
    forumsOrders(forum['gid'], forum['order']);
}

/**
 * Delete group/forum
 */
function delForums()
{
    if (currentAction == 'Groups') {
        var gid = _('gid').value;
        var msg = confirmGroupDelete;
        msg = msg.substr(0,  msg.indexOf('%s%'))+
              _('group_'+gid).getElementsByTagName('a')[0].innerHTML+
              msg.substr(msg.indexOf('%s%') + 3);
        if (confirm(msg)) {
            cacheForumForm = null;
            var response = ForumsAjax.callSync('deletegroup', gid);
            if (response['css'] == 'notice-message') {
                Element.destroy(_('group_'+gid));
                stopAction();
            }
            showResponse(response);
        }
    } else {
        var fid = _('fid').value;
        var msg = confirmForumDelete;
        msg = msg.substr(0, msg.indexOf('%s%'))+
              _('forum_'+fid).getElementsByTagName('a')[0].innerHTML+
              msg.substr(msg.indexOf('%s%') + 3);
        if (confirm(msg)) {
            var response = ForumsAjax.callSync('deleteforum', fid);
            if (response['css'] == 'notice-message') {
                Element.destroy(_('forum_'+fid));
                stopAction();
            }
            showResponse(response);
        }
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

    switch(currentAction) {
        case 'Forums':
            _('fid').value = 0;
            break;
        case 'Groups':
            _('gid').value = 0;
            break;
    }

    currentAction = null;
    unselectTreeRow();
    _('forums_edit').innerHTML = '';
    _('work_area_title').innerHTML = '';
}

var ForumsAjax = new JawsAjax('Forums', ForumsCallback);

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
