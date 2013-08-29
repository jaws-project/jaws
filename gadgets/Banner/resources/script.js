/**
 * Banner Javascript actions
 *
 * @category   Ajax
 * @package    Banner
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var BannerCallback = {
    insertbanner: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            _('banners_datagrid').addItem();
            _('banners_datagrid').setCurrentPage(0);
            getDG('banners_datagrid');
        }
        showResponse(response);
    },

    updatebanner: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG('banners_datagrid');
        }
        showResponse(response);
    },

    deletebanner: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            _('banners_datagrid').deleteItem();
            getDG('banners_datagrid');
        }
        showResponse(response);
    },

    resetviews: function(response) {
        if (response[0]['css'] == 'notice-message') {
            getDG('reports_datagrid');
        }
        showResponse(response);
    },

    resetclicks: function(response) {
        if (response[0]['css'] == 'notice-message') {
            getDG('reports_datagrid');
        }
        showResponse(response);
    },

    insertgroup: function(response) {
        if (response[0]['css'] == 'notice-message') {
            getGroups();
            stopAction();
        }
        showResponse(response);
    },

    updategroup: function(response) {
        if (response[0]['css'] == 'notice-message') {
            getGroups();
            stopAction();
        }
        showResponse(response);
    },

    deletegroup: function(response) {
        if (response[0]['css'] == 'notice-message') {
            getGroups();
            stopAction();
        }
        showResponse(response);
    },

    addbannerstogroup: function(response) {
        showResponse(response);
    }
}

function isValidURL(url) {
    return (/^(((ht|f)tp(s?))\:\/\/).*$/.test(url));
}

/**
 * Select DataGrid row
 *
 */
function selectDataGridRow(rowElement)
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRowColor = rowElement.style.backgroundColor;
    rowElement.style.backgroundColor = '#ffffcc';
    selectedRow = rowElement;
}

/**
 * Unselect DataGrid row
 *
 */
function unselectDataGridRow()
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRow = null;
    selectedRowColor = null;
}

/**
 * Fetches banners data to fills the data grid
 */
function getBannersDataGrid(name, offset, reset)
{
    var banners = BannerAjax.callSync('getbannersdatagrid', name, offset, _('bgroup_filter').value);
    if (reset) {
        stopAction();
        _(name).setCurrentPage(0);
        var total = BannerAjax.callSync('getbannerscount', _('bgroup_filter').value);
    }

    resetGrid(name, banners, total);
}

function makeBigBannerEntry()
{
    var height = parseInt(_('banner').style.height.substr(0, _('banner').style.height.length-2));
    height += 10;
    _('banner').style.height = height + 'px';
}

function setTemplate(template)
{
    _('template').value = template;
    _('template').focus();
}

function changeThroughUpload(checked) {
    if (checked) {
        _('banner').style.display = 'none';
        _('upload_banner').style.display = 'inline';
    } else {
        _('upload_banner').style.display = 'none';
        _('banner').style.display = 'inline';
    }
}

/**
 * Get groups list
 */
function getGroups()
{
    resetCombo(_('groups_combo'));
    var groupList = BannerAjax.callSync('getgroups', -1, -1);
    if (groupList != false) {
        var combo = _('groups_combo');
        var i = 0;
        groupList.each(function(value, index) {
            var op = new Option(value['title'].defilter(), value['id']);
            if (i % 2 == 0) {
                op.style.backgroundColor = evenColor;
            } else {
                op.style.backgroundColor = oddColor;
            }
            combo.options[combo.options.length] = op;
            i++;
        });
    }
}

/**
 * Resets the banners list
 */
function resetCombo(combo)
{
    while(combo.options.length != 0) {
        combo.options[0] = null;
    }
}
/**
 * can submit form
*/
function submit_banner()
{
    return can_submit;
}

/**
 * Saves data / changes
 */
function saveBanner()
{
    if (_('title').value.blank() ||
        _('template').value.blank() ||
        _('gid').value == 0)
    {
        alert(incompleteBannerFields);
        return false;
    }

    if (document.getElementsByName('through_upload[]').item(0).checked) {
        can_submit = true;
        document.banner_info.submit();
        return true;
    } else {
        if (_('bid').value == 0) {
            BannerAjax.callAsync('insertbanner',
                                 _('title').value,
                                 _('url').value,
                                 _('gid').value,
                                 _('banner').value,
                                 _('template').value,
                                 _('views_limit').value,
                                 _('clicks_limit').value,
                                 _('start_time').value,
                                 _('stop_time').value,
                                 _('random').value,
                                 _('published').value);
        } else {
            BannerAjax.callAsync('updatebanner',
                                _('bid').value,
                                _('title').value,
                                _('url').value,
                                _('gid').value,
                                _('banner').value,
                                _('template').value,
                                _('views_limit').value,
                                _('clicks_limit').value,
                                _('start_time').value,
                                _('stop_time').value,
                                _('random').value,
                                _('published').value);
        }
    }
}

/**
 * Saves data / changes on the group's form
 */
function saveGroup()
{
    if (currentAction == 'ManageGroupBanners') {
        var box  = _('group_members');
        var keys = new Array();
        for(var i = 0; i < box.length; i++) {
            keys[i] = box.options[i].value;
        }
        BannerAjax.callAsync('addbannerstogroup', selectedGroup, keys);
    } else {
        if (_('title').value.blank()) {
            alert(incompleteGroupFields);
            return false;
        }

        if (selectedGroup == null) {
            _('gid').value = 0;
            BannerAjax.callAsync(
                            'insertgroup',
                            _('title').value,
                            _('count').value,
                            _('show_title').value,
                            _('show_type').value,
                            _('published').value);
        } else {
            _('gid').value = selectedGroup;
            BannerAjax.callAsync(
                            'updategroup',
                            _('gid').value,
                            _('title').value,
                            _('count').value,
                            _('show_title').value,
                            _('show_type').value,
                            _('published').value);
        }
    }
}

/**
 * Delete user
 */
function deleteBanner(element, bid)
{
    stopAction();
    selectDataGridRow(element.parentNode.parentNode);
    var answer = confirm(confirmBannerDelete);
    if (answer) {
        BannerAjax.callAsync('deletebanner', bid);
    }
    unselectDataGridRow();
}

/**
 * Reset Views Counter
 */
function resetViews(bid)
{
    var answer = confirm(confirmResetBannerViews);
    if (answer) {
        BannerAjax.callAsync('resetviews', bid);
    }
}

/**
 * Reset Clicks Counter
 */
function resetClicks(bid)
{
    var answer = confirm(confirmResetBannerClicks);
    if (answer) {
        BannerAjax.callAsync('resetclicks', bid);
    }
}

/**
 * Delete group
 */
function deleteGroup()
{
    var answer = confirm(confirmGroupDelete);
    if (answer) {
        BannerAjax.callAsync('deletegroup', selectedGroup);
    }
}

/**
 * Add group
 */
function addGroup()
{
    if (cacheMasterForm == null) {
        cacheMasterForm = BannerAjax.callSync('getgroupui');
    }
    currentAction = 'AddGroup';

    _('cancel_action').style.display = 'inline';
    _('save_group').style.display = 'inline';
    _('add_banners').style.display = 'none';
    _('add_group').style.display = 'none';
    _('group_area').innerHTML = cacheMasterForm;
    selectedGroup = null;
}

/**
 * Edit banner
 */
function editBanner(element, bid)
{
    if (bid == 0) return;
    currentAction = 'Banners';
    _('legend_title').innerHTML = editBanner_title;

    selectDataGridRow(element.parentNode.parentNode);

    var banner = BannerAjax.callSync('getbanner', bid);
    _('bid').value    = banner['id'];
    _('title').value  = banner['title'].defilter();
    _('url').value    = banner['url'];
    _('gid').value    = banner['gid'];
    document.getElementsByName('through_upload[]').item(0).checked = false;
    _('banner').value       = banner['banner'].defilter();
    defaultTemplate         = banner['template'];
    _('template').value     = defaultTemplate;
    defaultTemplate = banner['template'];
    _('views_limit').value  = banner['views_limitation'];
    _('clicks_limit').value = banner['clicks_limitation'];
    if (banner['start_time'] == null) banner['start_time'] = '';
    if (banner['stop_time']  == null) banner['stop_time']  = '';
    _('start_time').value   = banner['start_time'];
    _('stop_time').value    = banner['stop_time'];
    _('random').selectedIndex  = banner['random'];
    _('published').selectedIndex = banner['published']? 1 : 0;
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    if (cacheMasterForm == null) {
        cacheMasterForm = BannerAjax.callSync('getgroupui');
    }

    _('group_banners_area').innerHTML = '';
    currentAction = 'EditGroup';
    _('cancel_action').style.display = 'inline';
    _('save_group').style.display = 'inline';
    _('add_banners').style.display = 'inline';
    _('delete_group').style.display = 'inline';
    _('add_group').style.display = 'none';
    _('group_area').innerHTML = cacheMasterForm;
    selectedGroup = gid;
    var groupInfo = BannerAjax.callSync('getgroup', selectedGroup);
    _('gid').value   = groupInfo['id'];
    _('title').value = groupInfo['title'].defilter();
    _('count').value = groupInfo['limit_count'];
    _('show_title').value = groupInfo['show_title']? 1 : 0;
    _('show_type').value  = groupInfo['show_type'];
    _('published').selectedIndex = groupInfo['published']? 1 : 0;
}

/**
 *
 */
function AddableBanner()
{
    var banners = _('banners_combo');
    if (banners.selectedIndex == -1) return false;

    var box = _('group_members');
    if (box.options.length == 0) return true;

    var value = banners.options[banners.selectedIndex].value;

    for (i=0; i<box.options.length; i++) {
        if (box.options[i].value == value) return false;
    }

    return true;
}

/**
 *
 */
function addBannerToList()
{
    if (!AddableBanner()) return;
    var banners = _('banners_combo');
    var title = banners.options[banners.selectedIndex].text;
    var value = banners.options[banners.selectedIndex].value;
    var box = _('group_members');
    box.options[box.options.length] = new Option(title, value);
}

/**
 *
 */
function delBannerFromList()
{
    var box = _('group_members');
    if (box.selectedIndex != -1) {
        box.options[box.selectedIndex] = null;
    }
}

/**
 *
 */
function upBannerRank()
{
    var box = _('group_members');
    if (box.selectedIndex < 1) return;
    var tmpText  = box.options[box.selectedIndex - 1].text;
    var tmpValue = box.options[box.selectedIndex - 1].value;
    box.options[box.selectedIndex - 1].text  = box.options[box.selectedIndex].text;
    box.options[box.selectedIndex - 1].value = box.options[box.selectedIndex].value;
    box.options[box.selectedIndex].text  = tmpText;
    box.options[box.selectedIndex].value = tmpValue;
    box.selectedIndex  = box.selectedIndex - 1;
}

/**
 *
 */
function downBannerRank()
{
    var box = _('group_members');
    if (box.selectedIndex == -1) return;
    if (box.selectedIndex > box.length-2) return;
    var tmpText  = box.options[box.selectedIndex + 1].text;
    var tmpValue = box.options[box.selectedIndex + 1].value;
    box.options[box.selectedIndex + 1].text  = box.options[box.selectedIndex].text;
    box.options[box.selectedIndex + 1].value = box.options[box.selectedIndex].value;
    box.options[box.selectedIndex].text  = tmpText;
    box.options[box.selectedIndex].value = tmpValue;
    box.selectedIndex  = box.selectedIndex + 1;
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    switch(currentAction) {
    case 'Banners':
        _('legend_title').innerHTML = addBanner_title;
        defaultTemplate         = '';
        _('bid').value          = 0;
        _('title').value        = '';
        _('url').value          = 'http://';
        _('banner').value       = '';
        _('upload_banner').value= '';
        _('template').value     = '';
        _('views_limit').value  = '';
        _('clicks_limit').value = '';
        _('start_time').value   = '';
        _('stop_time').value    = '';
        _('random').value       = 0;
        _('published').value    = 1;
        unselectDataGridRow();
        break;
    case 'EditGroup':
    case 'AddGroup':
        _('add_group').style.display = 'inline';
        _('save_group').style.display = 'none';
        _('add_banners').style.display = 'none';
        _('delete_group').style.display = 'none';
        _('groups_combo').selectedIndex = -1;
        _('group_area').innerHTML = '';
        _('cancel_action').style.display = 'none';
        selectedGroup = null;
        break;
    case 'ManageGroupBanners':
        currentAction = 'EditGroup';
        _('cancel_action').style.display = 'inline';
        _('save_group').style.display = 'inline';
        _('add_banners').style.display = 'inline';
        _('delete_group').style.display = 'inline';
        _('add_group').style.display = 'none';
        _('group_banners_area').innerHTML = '';
        //--
        _('title').disabled   = false;
        _('count').disabled   = false;
        _('published').disabled = false;
        //--
        break;
    case 'ViewReports':
        break;
    }
}

/**
 * Show a simple-form with checkboxes so banners can check their group
 */
function editGroupBanners()
{
    if (selectedGroup == null) {return;}
    if (cacheSlaveForm == null) {
        cacheSlaveForm = BannerAjax.callSync('getgroupbannersui');
    }
    _('save_group').style.display = 'inline';
    _('add_banners').style.display = 'none';
    _('delete_group').style.display = 'none';
    _('group_banners_area').innerHTML = cacheSlaveForm;
    //--
    _('title').disabled   = true;
    _('count').disabled   = true;
    _('published').disabled = true;
    //--
    currentAction = 'ManageGroupBanners';
    var banners = BannerAjax.callSync('getbanners', -1, selectedGroup);
    var box = _('group_members');
    box.length = 0;
    for(var i = 0; i < banners.length; i++) {
        box.options[i] = new Option(banners[i]['title'] +' '+'('+banners[i]['url']+')', banners[i]['id']);
    }
}

var BannerAjax = new JawsAjax('Banner', BannerCallback);

// can for submit?
var can_submit = false;
//current group
var selectedGroup = null;
//Combo colors
var evenColor = '#fff';
var oddColor  = '#edf3fe';

//Cache for saving the group|banner-form template
var cacheSlaveForm = null;

//Cache for saving the group|banner-form template
var cacheMasterForm = null;
//Cache for group-banner management
var cacheBannerGroupForm = null;

//Which action are we runing?
var currentAction = null;

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;