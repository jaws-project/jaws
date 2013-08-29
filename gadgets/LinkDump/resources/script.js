/**
 * LinkDump Javascript actions
 *
 * @category   Ajax
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var LinkDumpCallback = { 
    updategroup: function(response) {
        if (response[0]['css'] == 'notice-message') {
            _('group_'+_('gid').value).getElementsByTagName('a')[1].innerHTML = _('title').value;
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
        if (_('title').value.blank()) {
            alert(incompleteFields);
            return false;
        }

        lc = parseInt(_('limit_count').value);
        _('limit_count').value = (lc < 1)? '1' : ((lc > max_limit_count)? max_limit_count : lc);

        cacheLinkForm = null;
        if (selectedGroup == null) {
            var response = LinkDumpAjax.callSync('insertgroup',
                                                 _('title').value,
                                                 _('fast_url').value,
                                                 _('limit_count').value,
                                                 _('links_type').value,
                                                 _('order_type').value);
            if (response[0]['css'] == 'notice-message') {
                var gid = response[0]['data'];
                AddNewGroup(gid);
                stopAction();
            }
            showResponse(response);
        } else {
            LinkDumpAjax.callAsync('updategroup',
                                    _('gid').value,
                                    _('title').value,
                                    _('fast_url').value,
                                    _('limit_count').value,
                                    _('links_type').value,
                                    _('order_type').value);
        }
    } else {
        if (_('title').value.blank()) {
            alert(incompleteFields);
            return false;
        }
        if (selectedLink == null) {
            var response = LinkDumpAjax.callSync('insertlink',
                                                 _('gid').value,
                                                 _('title').value,
                                                 _('url').value,
                                                 _('fast_url').value,
                                                 _('description').value,
                                                 _('tags').value,
                                                 _('rank').value);
            if (response[0]['css'] == 'notice-message') {
                var lid = response[0]['data'];
                AddNewLinkItem(_('gid').value, lid, _('rank').value);
                stopAction();
            }
            showResponse(response);
        } else {
            var response = LinkDumpAjax.callSync('updatelink',
                                                 _('lid').value,
                                                 _('gid').value,
                                                 _('title').value,
                                                 _('url').value,
                                                 _('fast_url').value,
                                                 _('description').value,
                                                 _('tags').value,
                                                 _('rank').value);
            if (response[0]['css'] == 'notice-message') {
                _('link_'+_('lid').value).getElementsByTagName('a')[0].innerHTML = _('title').value;
                var new_parent = _('links_group_'+_('gid').value);
                var old_parent = _('link_'+_('lid').value).parentNode;
                var links_elements = new_parent.getElementsByTagName('div');
                if (old_parent != new_parent) {
                    if (_('rank').value > (links_elements.length - 1)) {
                        new_parent.appendChild(_('link_'+_('lid').value));
                    } else {
                        new_parent.insertBefore(_('link_'+_('lid').value), links_elements[_('rank').value - 1]);
                    }

                    if (old_parent.innerHTML.blank()) {
                        old_parent.innerHTML = noLinkExists;
                    }
                } else {
                    var oldRank = Array.from(links_elements).indexOf(_('link_'+_('lid').value)) + 1;
                    if (_('rank').value > oldRank) {
                        new_parent.insertBefore(_('link_'+_('lid').value), links_elements[_('rank').value]);
                    } else {
                        new_parent.insertBefore(_('link_'+_('lid').value), links_elements[_('rank').value - 1]);
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
    var cloneDiv =_('group_1').getElementsByTagName('div')[0].cloneNode(true);
    mainDiv.className = 'links_group';
    mainDiv.id = "group_"+gid;
    mainDiv.appendChild(cloneDiv);
    _('links_tree').appendChild(mainDiv);
    var links = mainDiv.getElementsByTagName('a');
    links[0].href      = 'javascript: listLinks('+gid+');';
    links[1].href = 'javascript: editGroup('+gid+');';
    links[1].innerHTML = _('title').value;
    links[2].href = 'javascript: addLink('+gid+');';

    var linksDiv = document.createElement('div');
    linksDiv.className = 'links';
    linksDiv.id = "links_group_"+gid;
    mainDiv.appendChild(linksDiv);
}

function AddNewLinkItem(gid, lid, rank)
{
    gLinksDiv = _('links_group_'+gid);
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
    var oldRank = Array.from(gLinksDiv.getElementsByTagName('div')).indexOf(_('link_'+lid));
    if (rank < oldRank) {
        gLinksDiv.insertBefore(_('link_'+lid), gLinksDiv.getElementsByTagName('div')[rank -1]);
    }
    //--

    var anchor = document.createElement('a');
    anchor.setAttribute('href', 'javascript:void(0);') 
    anchor.onclick = function() {
                        editLink(this, lid);
                     }
    anchor.innerHTML = _('title').value;
    mainDiv.appendChild(anchor);
}

function listLinks(gid, force_open)
{
    gNode = _('group_'+gid);
    gFlagimage = gNode.getElementsByTagName('img')[0];
    divSubList = _('links_group_'+gid);
    if (divSubList.innerHTML == '') {
        var links_list = LinkDumpAjax.callSync('getlinkslist', gid);
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
    _('rank').options.length = 0;

    var new_parentNode = _('links_group_'+pid);
    var rank = new_parentNode.getElementsByTagName('div').length;

    if ((_('lid').value < 1) || (_('link_'+_('lid').value).parentNode != new_parentNode)) {
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
 * Stops doing a certain action
 */
function stopAction()
{
    _('btn_cancel').style.display = 'none';
    _('btn_del').style.display    = 'none';
    _('btn_save').style.display   = 'none';
    _('btn_add').style.display    = 'inline';
    selectedLink  = null;
    selectedGroup = null;
    currentAction = null;
    unselectTreeRow();
    _('links_edit').innerHTML = '';
    _('edit_area').getElementsByTagName('span')[0].innerHTML = '';
}

/**
 * Add group
 */
function addGroup()
{
    if (cacheGroupForm == null) {
        cacheGroupForm = LinkDumpAjax.callSync('getgroupui');
    }
    currentAction = 'Groups';

    _('edit_area').getElementsByTagName('span')[0].innerHTML = addGroupTitle;
    selectedGroup = null;
    _('btn_cancel').style.display = 'inline';
    _('btn_del').style.display    = 'none';
    _('btn_save').style.display   = 'inline';
    _('btn_add').style.display    = 'none';
    _('links_edit').innerHTML = cacheGroupForm;
}

/**
 * Add link
 */
function addLink(gid)
{
    if (_('links_group_'+gid).innerHTML == '') {
        listLinks(gid);
    }
    if (cacheLinkForm == null) {
        cacheLinkForm = LinkDumpAjax.callSync('getlinkui');
    }
    stopAction();
    currentAction = 'Links';

    _('edit_area').getElementsByTagName('span')[0].innerHTML =
        addLinkTitle + ' - ' + _('group_'+gid).getElementsByTagName('a')[1].innerHTML;

    selectedLink = null;
    _('btn_cancel').style.display = 'inline';
    _('btn_del').style.display    = 'none';
    _('btn_save').style.display   = 'inline';
    _('btn_add').style.display    = 'none';
    _('links_edit').innerHTML = cacheLinkForm;

    _('gid').value = gid;
    setRanksCombo(_('gid').value);
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    unselectTreeRow();
    if (cacheGroupForm == null) {
        cacheGroupForm = LinkDumpAjax.callSync('getgroupui');
    }
    currentAction = 'Groups';
    selectedGroup = gid;

    _('edit_area').getElementsByTagName('span')[0].innerHTML =
        editGroupTitle + ' - ' + _('group_'+gid).getElementsByTagName('a')[1].innerHTML;
    _('btn_cancel').style.display = 'inline';
    _('btn_del').style.display    = 'inline';
    _('btn_save').style.display   = 'inline';
    _('btn_add').style.display    = 'none';
    _('links_edit').innerHTML   = cacheGroupForm;

    var groupInfo = LinkDumpAjax.callSync('getgroups', selectedGroup);

    _('gid').value         = groupInfo['id'];
    _('title').value       = groupInfo['title'].defilter();
    _('fast_url').value    = groupInfo['fast_url'];
    _('limit_count').value = groupInfo['limit_count'];
    _('links_type').value  = groupInfo['link_type'];
    _('order_type').value  = groupInfo['order_type'];
}

/**
 * Edit menu
 */
function editLink(element, lid)
{
    if (lid == 0) return;
    selectTreeRow(element.parentNode);
    if (cacheLinkForm == null) {
        cacheLinkForm = LinkDumpAjax.callSync('getlinkui');
    }
    currentAction = 'Links';
    selectedLink = lid;

    _('edit_area').getElementsByTagName('span')[0].innerHTML =
        editLinkTitle + ' - ' + _('link_'+lid).getElementsByTagName('a')[0].innerHTML;
    _('btn_cancel').style.display = 'inline';
    _('btn_del').style.display    = 'inline';
    _('btn_save').style.display   = 'inline';
    _('btn_add').style.display    = 'none';
    _('links_edit').innerHTML = cacheLinkForm;

    var linkInfo = LinkDumpAjax.callSync('getlink', selectedLink);

    _('lid').value         = linkInfo['id'];
    _('gid').value         = linkInfo['gid'];
    _('title').value       = linkInfo['title'].defilter();
    _('url').value         = linkInfo['url'];
    _('fast_url').value    = linkInfo['fast_url'];
    _('description').value = linkInfo['description'].defilter();
    _('tags').value        = linkInfo['tags'];
    _('clicks').value      = linkInfo['clicks'];
    setRanksCombo(_('gid').value);
    _('rank').value = linkInfo['rank'];
}

/**
 * Delete group/link
 */
function delLinks()
{
    if (currentAction == 'Groups') {
        var gid = selectedGroup;
        var msg = confirmGroupDelete;
        msg = msg.substr(0,  msg.indexOf('%s%')) + _('group_'+gid).getElementsByTagName('a')[1].innerHTML + msg.substr(msg.indexOf('%s%')+3);
        if (confirm(msg)) {
            cacheMenuForm = null;
            var response = LinkDumpAjax.callSync('deletegroup', gid);
            if (response[0]['css'] == 'notice-message') {
                Element.destroy(_('group_'+gid));
            }
            stopAction();
            showResponse(response);
        }
    } else {
        var lid = selectedLink;
        var msg = confirmLinkDelete;
        msg = msg.substr(0,  msg.indexOf('%s%'))+
              _('link_'+lid).getElementsByTagName('a')[0].innerHTML+
              msg.substr(msg.indexOf('%s%')+3);
        if (confirm(msg)) {
            var response = LinkDumpAjax.callSync('deletelink', lid, _('gid').value, _('rank').value);
            if (response[0]['css'] == 'notice-message') {
                link_parent = _('link_'+lid).parentNode;
                Element.destroy(_('link_'+lid));
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
    var lc = parseInt(_('limit_count').value);
    lc = isNaN(lc)? 0 : lc;
    lc++;
    lc = (lc < 1)? 1 : ((lc > max_limit_count)? max_limit_count : lc);
    _('limit_count').value = lc;
}

function downCount()
{
    var lc = parseInt(_('limit_count').value);
    lc = isNaN(lc)? 0 : lc;
    lc--;
    lc = (lc < 1)? 1 : ((lc > max_limit_count)? max_limit_count : lc);
    _('limit_count').value = lc;
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
