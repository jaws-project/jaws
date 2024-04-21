/**
 * LinkDump Javascript actions
 *
 * @category   Ajax
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
function Jaws_Gadget_LinkDump() { return {
    // ASync callback method
    AjaxCallback : {
    },
}};
/**
 * Use async mode, create Callback
 */
var LinkDumpCallback = { 
    UpdateGroup: function(response) {
        if (response['type'] == 'alert-success') {
            $('#group_'+$('#gid').val()).find('a').eq(1).html($('#title').val());
            stopAction();
        }
    }
}

/**
 * Select Tree row
 *
 */
function selectTreeRow(rowElement)
{
    if (selectedRow) {
        selectedRow.css('background-color', selectedRowColor);
    }
    selectedRowColor = rowElement.css('background-color');
    rowElement.css('background-color', '#eeeecc');
    selectedRow = rowElement;
}

/**
 * Unselect Tree row
 *
 */
function unselectTreeRow()
{
    if (selectedRow) {
        selectedRow.css('background-color', selectedRowColor);
    }
    selectedRow = null;
    selectedRowColor = null;
}

/**
 * Saves data / changes
 */
function saveLink()
{
    if (currentAction == 'Groups') {
        if (!$('#title').val()) {
            alert(Jaws.gadgets.LinkDump.defines.incompleteFields);
            return false;
        }

        lc = parseInt($('#limit_count').val());
        $('#limit_count').val((lc < 1)? '1' : ((lc > Jaws.gadgets.LinkDump.defines.max_limit_count)? Jaws.gadgets.LinkDump.defines.max_limit_count : lc));

        cacheLinkForm = null;
        if (selectedGroup == null) {
            var response = LinkDumpAjax.call(
                'InsertGroup', [
                    $('#title').val(),
                    $('#fast_url').val(),
                    $('#limit_count').val(),
                    $('#links_type').val(),
                    $('#order_type').val()
                ],
                false, {'async': false}
            );
            if (response['type'] == 'alert-success') {
                var gid = response['data'];
                AddNewGroup(gid);
                stopAction();
            }
        } else {
            LinkDumpAjax.call(
                'UpdateGroup', [
                    $('#gid').val(),
                    $('#title').val(),
                    $('#fast_url').val(),
                    $('#limit_count').val(),
                    $('#links_type').val(),
                    $('#order_type').val()
                ]
            );
        }
    } else {
        if (!$('#title').val()) {
            alert(Jaws.gadgets.LinkDump.defines.incompleteFields);
            return false;
        }
        var tags = "";
        if ($('#tags').length) {
            tags = $('#tags').val();
        }
        if (selectedLink == null) {
            var response = LinkDumpAjax.call(
                'InsertLink', [
                    $('#gid').val(),
                    $('#title').val(),
                    $('#url').val(),
                    $('#fast_url').val(),
                    $('#description').val(),
                    tags,
                    $('#rank').val()
                ],
                false, {'async': false}
            );
            if (response['type'] == 'alert-success') {
                var lid = response['data'];
                AddNewLinkItem($('#gid').val(), lid, $('#rank').val());
                stopAction();
            }
        } else {
            var response = LinkDumpAjax.call(
                'UpdateLink', [
                    $('#lid').val(),
                    $('#gid').val(),
                    $('#title').val(),
                    $('#url').val(),
                    $('#fast_url').val(),
                    $('#description').val(),
                    tags,
                    $('#rank').val()
                ],
                false, {'async': false}
            );
            if (response['type'] == 'alert-success') {
                $('#link_'+$('#lid').val()).find('a').first().html($('#title').val());
                var new_parent = $('#links_group_'+$('#gid').val());
                var old_parent = $('#link_'+$('#lid').val()).parent();
                var links_elements = new_parent.find('div');
                if (old_parent.is(new_parent)) {
                    if ($('#rank').val() == links_elements.length) {
                        $('#link_'+$('#lid').val()).insertAfter(links_elements.eq($('#rank').val()-1));
                    } else {
                        $('#link_'+$('#lid').val()).insertBefore(links_elements.eq($('#rank').val()-1));
                    }
                } else {
                    if ($('#rank').val() > (links_elements.length)) {
                        new_parent.append($('#link_'+$('#lid').val()));
                    } else {
                        $('#link_'+$('#lid').val()).insertBefore(links_elements.eq($('#rank').val() - 1));
                    }

                    if (old_parent.html() == "") {
                        old_parent.html(Jaws.gadgets.LinkDump.defines.noLinkExists);
                    }
                }
                stopAction();
            }
        }
    }
}

function AddNewGroup(gid) {
    $('#links_tree').append(
        $('<div>').attr({'id': "group_"+gid, 'class': 'links_group'}).append(
            $('#group_1').find('div').first().clone(true)
        )
    );
    $("#group_"+gid).find('a')
        .first().attr('href', 'javascript:listLinks('+gid+');')
        .next().attr('href', 'javascript:editGroup('+gid+', 0);').html($('#title').val())
        .next().attr('href', 'javascript:addLink('+gid+', 0);');
    $("#group_"+gid).append(
        $('<div>').attr({'id': "links_group_"+gid, 'class': 'links'})
    );
}

/**
 * Add new link item
 */
function AddNewLinkItem(gid, lid, rank)
{

    gLinksDiv = $('#links_group_'+gid);
    if (gLinksDiv.html() == Jaws.gadgets.LinkDump.defines.noLinkExists) {
        gLinksDiv.html('');
    }

    var mainDiv = $('<div>').attr('id', "link_"+lid).append(
        $('<img>').attr({'class': 'icon', 'src': Jaws.gadgets.LinkDump.defines.linkImageSrc})
    ).append(
        $('<a>').attr('href', 'javascript:void(0);')
            .html($('#title').val())
            .on('click', function() {editLink(this, lid);})
    );

    //set ranking
    var link_elements = gLinksDiv.children('div');
    var oldRank = link_elements.length;
    if (rank < oldRank) {
        mainDiv.insertBefore(link_elements.eq(rank -1));
    } else {
        gLinksDiv.append(mainDiv);
    }
}

function listLinks(gid, force_open)
{
    gNode = $('#group_'+gid);
    gFlagimage = gNode.find('img').first();
    divSubList = $('#links_group_'+gid);
    if (divSubList.html() == '') {
        var links_list = LinkDumpAjax.call('GetLinksList', gid, false, {'async': false});
        if (links_list !== "") {
            divSubList.html(links_list);
        } else {
            divSubList.html(Jaws.gadgets.LinkDump.defines.noLinkExists);
        }
        gFlagimage.attr('src', Jaws.gadgets.LinkDump.defines.linksListCloseImageSrc);
    } else {
        if (force_open == null) {
            divSubList.html('');
            gFlagimage.attr('src', Jaws.gadgets.LinkDump.defines.linksListOpenImageSrc);
        }
    }
    if (force_open == null) {
        stopAction();
    }
}

function setRanksCombo(pid, selected) {
    listLinks(pid, true);
    $('#rank').empty();

    var new_parentNode = $('#links_group_'+pid);
    var rank = new_parentNode.find('div').length;

    if (($('#lid').val() < 1) || !$('#link_'+$('#lid').val()).parent().is(new_parentNode)) {
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
 * Stops doing a certain action
 */
function stopAction()
{
    $('#btn_cancel').css('display', 'none');
    $('#btn_del').css('display', 'none');
    $('#btn_save').css('display', 'none');
    $('#btn_add').css('display', 'inline');
    selectedLink  = null;
    selectedGroup = null;
    currentAction = null;
    unselectTreeRow();
    $('#links_edit').html('');
    $('#edit_area span').first().html('');
}

/**
 * Add group
 */
function addGroup()
{
    if (cacheGroupForm == null) {
        cacheGroupForm = LinkDumpAjax.call('GetGroupUI', {}, false, {'async': false});
    }
    currentAction = 'Groups';

    $('#edit_area span').first().html(Jaws.gadgets.LinkDump.defines.addGroupTitle);
    selectedGroup = null;
    $('#btn_cancel').css('display', 'inline');
    $('#btn_del').css('display', 'none');
    $('#btn_save').css('display', 'inline');
    $('#btn_add').css('display', 'none');
    $('#links_edit').html(cacheGroupForm);
}

/**
 * Add link
 */
function addLink(gid)
{
    if ($('#links_group_'+gid).html() == '') {
        listLinks(gid);
    }
    if (cacheLinkForm == null) {
        cacheLinkForm = LinkDumpAjax.call('GetLinkUI', {}, false, {'async': false});
    }
    stopAction();
    currentAction = 'Links';
    selectedLink = null;

    $('#edit_area span').first().html(Jaws.gadgets.LinkDump.defines.addLinkTitle + ' - ' + $('#group_'+gid+' a').first().next().html());
    $('#btn_cancel').css('display', 'inline');
    $('#btn_del').css('display', 'none');
    $('#btn_save').css('display', 'inline');
    $('#btn_add').css('display', 'none');
    $('#links_edit').html(cacheLinkForm);

    $('#gid').val(gid);
    setRanksCombo($('#gid').val());
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    unselectTreeRow();
    if (cacheGroupForm == null) {
        cacheGroupForm = LinkDumpAjax.call('GetGroupUI', {}, false, {'async': false});
    }
    currentAction = 'Groups';
    selectedGroup = gid;

    $('#edit_area span').first().html(Jaws.gadgets.LinkDump.defines.editGroupTitle + ' - ' + $('#group_'+gid + ' a').first().next().html());
    $('#btn_cancel').css('display', 'inline');
    $('#btn_del').css('display', 'inline');
    $('#btn_save').css('display', 'inline');
    $('#btn_add').css('display', 'none');
    $('#links_edit').html(cacheGroupForm);

    var groupInfo = LinkDumpAjax.call('GetGroups', selectedGroup, false, {'async': false});

    $('#gid').val(groupInfo['id']);
    $('#title').val(groupInfo['title'].defilter());
    $('#fast_url').val(groupInfo['fast_url']);
    $('#limit_count').val(groupInfo['limit_count']);
    $('#links_type').val(groupInfo['link_type']);
    $('#order_type').val(groupInfo['order_type']);
}

/**
 * Edit menu
 */
function editLink(element, lid)
{
    if (lid == 0) return;
    selectTreeRow($(element).parent());
    if (cacheLinkForm == null) {
        cacheLinkForm = LinkDumpAjax.call('GetLinkUI', {}, false, {'async': false});
    }
    currentAction = 'Links';
    selectedLink = lid;

    $('#edit_area span').first().html(Jaws.gadgets.LinkDump.defines.editLinkTitle + ' - ' + $('#link_'+lid + ' a').first().html());
    $('#btn_cancel').css('display', 'inline');
    $('#btn_del').css('display', 'inline');
    $('#btn_save').css('display', 'inline');
    $('#btn_add').css('display', 'none');
    $('#links_edit').html(cacheLinkForm);

    var linkInfo = LinkDumpAjax.call('GetLink', selectedLink, false, {'async': false});

    $('#lid').val(linkInfo['id']);
    $('#gid').val(linkInfo['gid']);
    $('#title').val(linkInfo['title'].defilter());
    $('#url').val(linkInfo['url']);
    $('#fast_url').val(linkInfo['fast_url']);
    $('#description').val(linkInfo['description'].defilter());
    if($('#tags').length) {
        $('#tags').val(linkInfo['tags']);
    }
    $('#clicks').val(linkInfo['clicks']);
    setRanksCombo($('#gid').val());
    $('#rank').val(linkInfo['rank']);
}

/**
 * Delete group/link
 */
function delLinks()
{
    if (currentAction == 'Groups') {
        var gid = selectedGroup;
        var msg = Jaws.gadgets.LinkDump.defines.confirmGroupDelete;
        msg = msg.substr(0,  msg.indexOf('%s%')) + $('group_'+gid).find('a').eq(1).html() + msg.substr(msg.indexOf('%s%')+3);
        if (confirm(msg)) {
            cacheMenuForm = null;
            var response = LinkDumpAjax.call('DeleteGroup', gid, false, {'async': false});
            if (response['type'] == 'alert-success') {
                $('#group_'+gid).remove();
            }
            stopAction();
        }
    } else {
        var lid = selectedLink;
        var msg = Jaws.gadgets.LinkDump.defines.confirmLinkDelete;
        msg = msg.substr(0,  msg.indexOf('%s%'))+
              $('link_'+lid).find('a').first().html()+
              msg.substr(msg.indexOf('%s%')+3);
        if (confirm(msg)) {
            var response = LinkDumpAjax.call('DeleteLink', [lid, $('#gid').val(), $('#rank').val()], false, {'async': false});
            if (response['type'] == 'alert-success') {
                link_parent = $('#link_'+lid).parent();
                $('#link_'+lid).remove();
                if (link_parent.html() == "") {
                    link_parent.html(Jaws.gadgets.LinkDump.defines.noLinkExists);
                }
            }
            stopAction();
        }
    }
}

function upCount()
{
    var lc = parseInt($('#limit_count').val());
    lc = isNaN(lc)? 0 : lc;
    lc++;
    lc = (lc < 1)? 1 : ((lc > Jaws.gadgets.LinkDump.defines.max_limit_count)? Jaws.gadgets.LinkDump.defines.max_limit_count : lc);
    $('#limit_count').val(lc);
}

function downCount()
{
    var lc = parseInt($('#limit_count').val());
    lc = isNaN(lc)? 0 : lc;
    lc--;
    lc = (lc < 1)? 1 : ((lc > Jaws.gadgets.LinkDump.defines.max_limit_count)? Jaws.gadgets.LinkDump.defines.max_limit_count : lc);
    $('#limit_count').val(lc);
}

var LinkDumpAjax = new JawsAjax('LinkDump', LinkDumpCallback);

//Current group
var selectedGroup = null;

//Current Link
var selectedLink = null;

//Cache for saving the group form template
var cacheGroupForm = null;

//Cache for saving the menu form template
var cacheLinkForm = null;

//Which row selected in Tree
var selectedRow = null;
var selectedRowColor = null;
