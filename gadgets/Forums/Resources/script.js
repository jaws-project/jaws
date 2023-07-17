/**
 * Forums JS actions
 *
 * @category   Ajax
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var ForumsCallback = {

    UpdateGroup: function(response) {
        if (response['type'] == 'alert-success') {
            $('#group_'+$('#gid').val()).find('a').first().html($('#title').val());
            stopAction();
        }
    }
}

/**
 * Select Tree row
 *
 */
function selectTreeRow(fid)
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    rowElement = $('#forum_' + fid).get(0);
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
    $('#forums_tree').append(
        $('<div>').attr({'id': "group_" + gid, 'class': 'forums_group_area'}).append(
            $('#group_1').find('div').first().clone(true)
        )
    );
    $("#group_" + gid).find('div.title a').attr('href', 'javascript: editGroup(' + gid + ');').html($('#title').val())
        .end().find('div.forum_actions a').attr('href', 'javascript: addForum(' + gid + ');');
}

/**
 *
 */
function AddNewForumItem(gid, fid, order)
{
    var parentNode = $('#group_'+gid);
    var forum_elements = parentNode.children('.forums_group_item');

    var oldOrder = parentNode.children('.forums_group_item').length;
    var mainDiv = $('<div>').attr({'id': "forum_" + fid, 'class': 'forums_group_item'}).append(
        $('<img>').attr({'src': Jaws.gadgets.Forums.defines.forumImageSrc, 'class': 'icon'}),
        $('<a>').attr({'href': 'javascript:editForum(this, ' + fid + ');', 'class': 'icon'}).html($('#title').val())
    );
    if (order < oldOrder) {
        mainDiv.insertBefore(forum_elements.eq(order - 1));
    } else {
        parentNode.append(mainDiv);
    }
}

/**
 * Saves data / changes
 */
function saveForums()
{
    if ($('#title').val().trim() == '') {
        alert(Jaws.gadgets.Forums.defines.incompleteFields);
        return false;
    }

    if (currentAction == 'Groups') {
        cacheForumForm = null;
        if ($('#gid').val() == 0) {
            var response = ForumsAjax.call(
                'InsertGroup', [
                    $('#title').val(),
                    $('#description').val(),
                    $('#fast_url').val(),
                    $('#order').val(),
                    $('#locked').val(),
                    $('#published').val()
                ],
                false, {'async': false}
            );
            if (response['type'] == 'alert-success') {
                AddNewForumGroup(response['data']);
                stopAction();
            }
        } else {
            ForumsAjax.call(
                'UpdateGroup', [
                    $('#gid').val(),
                    $('#title').val(),
                    $('#description').val(),
                    $('#fast_url').val(),
                    $('#order').val(),
                    $('#locked').val(),
                    $('#published').val()
                ]
            );
        }
    } else {
        if ($('#fid').val() == 0) {
            var response = ForumsAjax.call(
                'InsertForum', [
                    $('#gid').val(),
                    $('#title').val(),
                    $('#description').val(),
                    $('#fast_url').val(),
                    $('#order').val(),
                    $('#locked').val(),
                    $('#private').val(),
                    $('#published').val()
                ],
                false, {'async': false}
            );
            if (response['type'] == 'alert-success') {
                AddNewForumItem($('#gid').val(), response['data'], $('#order').val());
                stopAction();
            }
        } else {
            var response = ForumsAjax.call(
                'UpdateForum', [
                    $('#fid').val(),
                    $('#gid').val(),
                    $('#title').val(),
                    $('#description').val(),
                    $('#fast_url').val(),
                    $('#order').val(),
                    $('#locked').val(),
                    $('#private').val(),
                    $('#published').val()
                ],
                false, {'async': false}
            );
            if (response['type'] == 'alert-success') {
                $('#forum_'+$('#fid').val()).find('a').first().html($('#title').val());
                var new_parentNode = $('#group_'+$('#gid').val());
                if ($('#forum_'+$('#fid').val()).parent().is(new_parentNode)) {
                    new_parentNode.append($('#forum_'+$('#fid').val()));
                }
                stopAction();
            }
        }
    }
}

/**
 *
 */
function groupsOrders(selected) {
    $('#order').length = 0;
    var order = $('#forums_tree').find('div.forums_group_area').length;
    order = order + ((selected == null)? 1 : 0);
    for(var i = 0; i < order; i++) {
        $('#order').append($('<option>').val(i+1).text(i+1));
    }

    $('#order').val((selected == null)? order : selected);
}

/**
 *
 */
function forumsOrders(gid, selected) {
    $('#order').length = 0;
    var order = $('#group_'+gid).find('div.forums_group_item').length;
    order = order + ((selected == null)? 1 : 0);
    for(var i = 0; i < order; i++) {
        $('#order').append($('<option>').val(i+1).text(i+1));
    }

    $('#order').val((selected == null)? order : selected);
}

/**
 * Add group
 */
function addGroup()
{
    if (cacheGroupForm == null) {
        cacheGroupForm = ForumsAjax.call('GetGroupUI', {}, false, {'async': false});
    }
    currentAction = 'Groups';

    $('#work_area_title').html(Jaws.gadgets.Forums.defines.addGroupTitle);
    $('#btn_cancel').css('display', 'inline');
    $('#btn_del').css('display', 'none');
    $('#btn_save').css('display', 'inline');
    $('#btn_add').css('display', 'none');
    $('#forums_edit').html(cacheGroupForm);
    groupsOrders();
}

/**
 * Add forum
 */
function addForum(gid)
{
    if (cacheForumForm == null) {
        cacheForumForm = ForumsAjax.call('GetForumUI', {}, false, {'async': false});
    }

    stopAction();
    currentAction = 'Forums';
    $('#work_area_title').html(Jaws.gadgets.Forums.defines.addForumTitle + ' - ' + $('#group_'+gid + ' a').first().html());

    $('#btn_cancel').css('display', 'inline');
    $('#btn_del').css('display', 'none');
    $('#btn_save').css('display', 'inline');
    $('#btn_add').css('display', 'none');
    $('#forums_edit').html(cacheForumForm);
    $('#gid').val(gid);

    forumsOrders($('#gid').val());
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    if (cacheGroupForm == null) {
        cacheGroupForm = ForumsAjax.call('GetGroupUI', {}, false, {'async': false});
    }
    currentAction = 'Groups';

    $('#work_area_title').html(Jaws.gadgets.Forums.defines.editGroupTitle + ' - ' + $('#group_'+gid + ' a').first().html());
    $('#btn_cancel').css('display', 'inline');
    $('#btn_del').css('display', 'inline');
    $('#btn_save').css('display', 'inline');
    $('#btn_add').css('display', 'none');
    $('#forums_edit').html(cacheGroupForm);  

    var group = ForumsAjax.call('GetGroup', gid, false, {'async': false});

    $('#gid').val(group['id']);
    $('#title').val(group['title']);
    $('#description').val(group['description']);
    $('#fast_url').val(group['fast_url']);
    $('#locked').val(Number(group['locked']));
    $('#published').val(Number(group['published']));
    groupsOrders(group['order'])
}

/**
 * Edit forum
 */
function editForum(element, fid)
{
    if (fid == 0) return;
    selectTreeRow(fid);
    if (cacheForumForm == null) {
        cacheForumForm = ForumsAjax.call('GetForumUI', {}, false, {'async': false});
    }
    currentAction = 'Forums';

    $('#work_area_title').html(Jaws.gadgets.Forums.defines.editForumTitle + ' - ' + $('#forum_'+fid + ' a').first().html());
    $('#btn_cancel').css('display', 'inline');
    $('#btn_del').css('display', 'inline');
    $('#btn_save').css('display', 'inline');
    $('#btn_add').css('display', 'none');
    $('#forums_edit').html(cacheForumForm);  

    var forum = ForumsAjax.call('GetForum', fid, false, {'async': false});
    $('#fid').val(forum['id']);
    $('#gid').val(forum['gid']);
    $('#title').val(forum['title']);
    $('#description').val(forum['description']);
    $('#fast_url').val(forum['fast_url']);
    $('#locked').val(Number(forum['locked']));
    $('#private').val(Number(forum['private']));
    $('#published').val(Number(forum['published']));
    forumsOrders(forum['gid'], forum['order']);
}

/**
 * Delete group/forum
 */
function delForums()
{
    if (currentAction == 'Groups') {
        var gid = $('#gid').val();
        var msg = Jaws.gadgets.Forums.defines.confirmGroupDelete;
        msg = msg.substr(0,  msg.indexOf('%s%'))+
              $('#group_'+gid).find('a').first().html()+
              msg.substr(msg.indexOf('%s%') + 3);
        if (confirm(msg)) {
            cacheForumForm = null;
            var response = ForumsAjax.call('DeleteGroup', gid, false, {'async': false});
            if (response['type'] == 'alert-success') {
                $('#group_'+gid).remove();
            }
            stopAction();
        }
    } else {
        var fid = $('#fid').val();
        var msg = Jaws.gadgets.Forums.defines.confirmForumDelete;
        msg = msg.substr(0, msg.indexOf('%s%'))+
              $('#forum_'+fid).find('a').first().html()+
              msg.substr(msg.indexOf('%s%') + 3);
        if (confirm(msg)) {
            var response = ForumsAjax.call('DeleteForum', fid, false, {'async': false});
            if (response['type'] == 'alert-success') {
                $('#forum_'+fid).remove();
            }
            stopAction();
        }
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

    switch(currentAction) {
        case 'Forums':
            $('#fid').val(0);
            break;
        case 'Groups':
            $('#gid').val(0);
            break;
    }

    currentAction = null;
    unselectTreeRow();
    $('#forums_edit').html('');
    $('#work_area_title').html('');
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
