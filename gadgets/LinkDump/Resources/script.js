/**
 * LinkDump Javascript actions
 *
 * @category   Ajax
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var LinkDumpCallback = { 
    UpdateGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('group_'+$('gid').value).getElementsByTagName('a')[1].innerHTML = $('title').value;
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
    rowElement.style.backgroundColor = '#eeeecc';
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
 * Saves data / changes
 */
function saveLink()
{
    if (currentAction == 'Groups') {
        if ($('title').value.blank()) {
            alert(incompleteFields);
            return false;
        }

        lc = parseInt($('limit_count').value);
        $('limit_count').value = (lc < 1)? '1' : ((lc > max_limit_count)? max_limit_count : lc);

        cacheLinkForm = null;
        if (selectedGroup == null) {
            var response = LinkDumpAjax.callSync('InsertGroup',
                                                 $('title').value,
                                                 $('fast_url').value,
                                                 $('limit_count').value,
                                                 $('links_type').value,
                                                 $('order_type').value);
            if (response[0]['type'] == 'response_notice') {
                var gid = response[0]['data'];
                AddNewGroup(gid);
                stopAction();
            }
            showResponse(response);
        } else {
            LinkDumpAjax.callAsync('UpdateGroup',
                                    $('gid').value,
                                    $('title').value,
                                    $('fast_url').value,
                                    $('limit_count').value,
                                    $('links_type').value,
                                    $('order_type').value);
        }
    } else {
        if ($('title').value.blank()) {
            alert(incompleteFields);
            return false;
        }
        var tags = "";
        if($('tags')!=null) {
            tags = $('tags').value;
        }
        if (selectedLink == null) {
            var response = LinkDumpAjax.callSync(
                'InsertLink',
                $('gid').value,
                $('title').value,
                $('url').value,
                $('fast_url').value,
                $('description').value,
                tags,
                $('rank').value
            );
            if (response[0]['type'] == 'response_notice') {
                var lid = response[0]['data'];
                AddNewLinkItem($('gid').value, lid, $('rank').value);
                stopAction();
            }
            showResponse(response);
        } else {
            var response = LinkDumpAjax.callSync(
                'UpdateLink',
                $('lid').value,
                $('gid').value,
                $('title').value,
                $('url').value,
                $('fast_url').value,
                $('description').value,
                tags,
                $('rank').value
            );
            if (response[0]['type'] == 'response_notice') {
                $('link_'+$('lid').value).getElementsByTagName('a')[0].innerHTML = $('title').value;
                var new_parent = $('links_group_'+$('gid').value);
                var old_parent = $('link_'+$('lid').value).parentNode;
                var links_elements = new_parent.getElementsByTagName('div');
                if (old_parent != new_parent) {
                    if ($('rank').value > (links_elements.length - 1)) {
                        new_parent.appendChild($('link_'+$('lid').value));
                    } else {
                        new_parent.insertBefore($('link_'+$('lid').value), links_elements[$('rank').value - 1]);
                    }

                    if (old_parent.innerHTML.blank()) {
                        old_parent.innerHTML = noLinkExists;
                    }
                } else {
                    var oldRank = Array.from(links_elements).indexOf($('link_'+$('lid').value)) + 1;
                    if ($('rank').value > oldRank) {
                        new_parent.insertBefore($('link_'+$('lid').value), links_elements[$('rank').value]);
                    } else {
                        new_parent.insertBefore($('link_'+$('lid').value), links_elements[$('rank').value - 1]);
                    }
                }
                stopAction();
            }
            showResponse(response);
        }
    }
}

function AddNewGroup(gid) {
    var mainDiv = document.createElement('div');
    var cloneDiv =$('group_1').getElementsByTagName('div')[0].cloneNode(true);
    mainDiv.className = 'links_group';
    mainDiv.id = "group_"+gid;
    mainDiv.appendChild(cloneDiv);
    $('links_tree').appendChild(mainDiv);
    var links = mainDiv.getElementsByTagName('a');
    links[0].href      = 'javascript: listLinks('+gid+');';
    links[1].href = 'javascript: editGroup('+gid+');';
    links[1].innerHTML = $('title').value;
    links[2].href = 'javascript: addLink('+gid+');';

    var linksDiv = document.createElement('div');
    linksDiv.className = 'links';
    linksDiv.id = "links_group_"+gid;
    mainDiv.appendChild(linksDiv);
}

/**
 * Add new link item
 */
function AddNewLinkItem(gid, lid, rank)
{
    gLinksDiv = $('links_group_'+gid);
    var mainDiv = document.createElement('div');
    mainDiv.id = "link_"+lid;
    if (gLinksDiv.innerHTML == noLinkExists) {
        gLinksDiv.innerHTML = '';
    }
    gLinksDiv.appendChild(mainDiv);

    var img = document.createElement('img');
    img.className = 'icon';
    img.src = linkImageSrc;
    mainDiv.appendChild(img);

    //set ranking
    var oldRank = Array.from(gLinksDiv.getElementsByTagName('div')).indexOf($('link_'+lid));
    if (rank < oldRank) {
        gLinksDiv.insertBefore($('link_'+lid), gLinksDiv.getElementsByTagName('div')[rank -1]);
    }
    //--

    var anchor = document.createElement('a');
    anchor.setAttribute('href', 'javascript:void(0);') 
    anchor.onclick = function() {
                        editLink(this, lid);
                     }
    anchor.innerHTML = $('title').value;
    mainDiv.appendChild(anchor);
}

function listLinks(gid, force_open)
{
    gNode = $('group_'+gid);
    gFlagimage = gNode.getElementsByTagName('img')[0];
    divSubList = $('links_group_'+gid);
    if (divSubList.innerHTML == '') {
        var links_list = LinkDumpAjax.callSync('GetLinksList', gid);
        if (!links_list.blank()) {
            divSubList.innerHTML = links_list;
        } else {
            divSubList.innerHTML = noLinkExists;
        }
        gFlagimage.src = linksListCloseImageSrc;
    } else {
        if (force_open == null) {
            divSubList.innerHTML = '';
            gFlagimage.src = linksListOpenImageSrc;
        }
    }
    if (force_open == null) {
        stopAction();
    }
}

function setRanksCombo(pid, selected) {
    listLinks(pid, true);
    $('rank').options.length = 0;

    var new_parentNode = $('links_group_'+pid);
    var rank = new_parentNode.getElementsByTagName('div').length;

    if (($('lid').value < 1) || ($('link_'+$('lid').value).parentNode != new_parentNode)) {
        rank = rank + 1;
    }

    for(var i = 0; i < rank; i++) {
        $('rank').options[i] = new Option(i+1, i+1);
    }
    if (selected == null) {
        $('rank').value = rank;
    } else {
        $('rank').value = selected;
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
    selectedLink  = null;
    selectedGroup = null;
    currentAction = null;
    unselectTreeRow();
    $('links_edit').innerHTML = '';
    $('edit_area').getElementsByTagName('span')[0].innerHTML = '';
}

/**
 * Add group
 */
function addGroup()
{
    if (cacheGroupForm == null) {
        cacheGroupForm = LinkDumpAjax.callSync('GetGroupUI');
    }
    currentAction = 'Groups';

    $('edit_area').getElementsByTagName('span')[0].innerHTML = addGroupTitle;
    selectedGroup = null;
    $('btn_cancel').style.display = 'inline';
    $('btn_del').style.display    = 'none';
    $('btn_save').style.display   = 'inline';
    $('btn_add').style.display    = 'none';
    $('links_edit').innerHTML = cacheGroupForm;
}

/**
 * Add link
 */
function addLink(gid)
{
    if ($('links_group_'+gid).innerHTML == '') {
        listLinks(gid);
    }
    if (cacheLinkForm == null) {
        cacheLinkForm = LinkDumpAjax.callSync('GetLinkUI');
    }
    stopAction();
    currentAction = 'Links';

    $('edit_area').getElementsByTagName('span')[0].innerHTML =
        addLinkTitle + ' - ' + $('group_'+gid).getElementsByTagName('a')[1].innerHTML;

    selectedLink = null;
    $('btn_cancel').style.display = 'inline';
    $('btn_del').style.display    = 'none';
    $('btn_save').style.display   = 'inline';
    $('btn_add').style.display    = 'none';
    $('links_edit').innerHTML = cacheLinkForm;

    $('gid').value = gid;
    setRanksCombo($('gid').value);
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    unselectTreeRow();
    if (cacheGroupForm == null) {
        cacheGroupForm = LinkDumpAjax.callSync('GetGroupUI');
    }
    currentAction = 'Groups';
    selectedGroup = gid;

    $('edit_area').getElementsByTagName('span')[0].innerHTML =
        editGroupTitle + ' - ' + $('group_'+gid).getElementsByTagName('a')[1].innerHTML;
    $('btn_cancel').style.display = 'inline';
    $('btn_del').style.display    = 'inline';
    $('btn_save').style.display   = 'inline';
    $('btn_add').style.display    = 'none';
    $('links_edit').innerHTML   = cacheGroupForm;

    var groupInfo = LinkDumpAjax.callSync('GetGroups', selectedGroup);

    $('gid').value         = groupInfo['id'];
    $('title').value       = groupInfo['title'].defilter();
    $('fast_url').value    = groupInfo['fast_url'];
    $('limit_count').value = groupInfo['limit_count'];
    $('links_type').value  = groupInfo['link_type'];
    $('order_type').value  = groupInfo['order_type'];
}

/**
 * Edit menu
 */
function editLink(element, lid)
{
    if (lid == 0) return;
    selectTreeRow(element.parentNode);
    if (cacheLinkForm == null) {
        cacheLinkForm = LinkDumpAjax.callSync('GetLinkUI');
    }
    currentAction = 'Links';
    selectedLink = lid;

    $('edit_area').getElementsByTagName('span')[0].innerHTML =
        editLinkTitle + ' - ' + $('link_'+lid).getElementsByTagName('a')[0].innerHTML;
    $('btn_cancel').style.display = 'inline';
    $('btn_del').style.display    = 'inline';
    $('btn_save').style.display   = 'inline';
    $('btn_add').style.display    = 'none';
    $('links_edit').innerHTML = cacheLinkForm;

    var linkInfo = LinkDumpAjax.callSync('GetLink', selectedLink);

    $('lid').value         = linkInfo['id'];
    $('gid').value         = linkInfo['gid'];
    $('title').value       = linkInfo['title'].defilter();
    $('url').value         = linkInfo['url'];
    $('fast_url').value    = linkInfo['fast_url'];
    $('description').value = linkInfo['description'].defilter();
    if($('tags')!=null) {
        $('tags').value    = linkInfo['tags'];
    }
    $('clicks').value      = linkInfo['clicks'];
    setRanksCombo($('gid').value);
    $('rank').value = linkInfo['rank'];
}

/**
 * Delete group/link
 */
function delLinks()
{
    if (currentAction == 'Groups') {
        var gid = selectedGroup;
        var msg = confirmGroupDelete;
        msg = msg.substr(0,  msg.indexOf('%s%')) + $('group_'+gid).getElementsByTagName('a')[1].innerHTML + msg.substr(msg.indexOf('%s%')+3);
        if (confirm(msg)) {
            cacheMenuForm = null;
            var response = LinkDumpAjax.callSync('DeleteGroup', gid);
            if (response[0]['type'] == 'response_notice') {
                Element.destroy($('group_'+gid));
            }
            stopAction();
            showResponse(response);
        }
    } else {
        var lid = selectedLink;
        var msg = confirmLinkDelete;
        msg = msg.substr(0,  msg.indexOf('%s%'))+
              $('link_'+lid).getElementsByTagName('a')[0].innerHTML+
              msg.substr(msg.indexOf('%s%')+3);
        if (confirm(msg)) {
            var response = LinkDumpAjax.callSync('DeleteLink', lid, $('gid').value, $('rank').value);
            if (response[0]['type'] == 'response_notice') {
                link_parent = $('link_'+lid).parentNode;
                Element.destroy($('link_'+lid));
                if (link_parent.innerHTML.blank()) {
                    link_parent.innerHTML = noLinkExists;
                }
            }
            stopAction();
            showResponse(response);
        }
    }
}

function upCount()
{
    var lc = parseInt($('limit_count').value);
    lc = isNaN(lc)? 0 : lc;
    lc++;
    lc = (lc < 1)? 1 : ((lc > max_limit_count)? max_limit_count : lc);
    $('limit_count').value = lc;
}

function downCount()
{
    var lc = parseInt($('limit_count').value);
    lc = isNaN(lc)? 0 : lc;
    lc--;
    lc = (lc < 1)? 1 : ((lc > max_limit_count)? max_limit_count : lc);
    $('limit_count').value = lc;
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
