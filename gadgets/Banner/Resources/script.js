/**
 * Banner Javascript actions
 *
 * @category   Ajax
 * @package    Banner
 */
/**
 * Use async mode, create Callback
 */
var BannerCallback = {
    InsertBanner: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            $('banners_datagrid').addItem();
            $('banners_datagrid').setCurrentPage(0);
            getDG('banners_datagrid');
        }
        showResponse(response);
    },

    UpdateBanner: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            getDG('banners_datagrid');
        }
        showResponse(response);
    },

    DeleteBanner: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            $('banners_datagrid').deleteItem();
            getDG('banners_datagrid');
        }
        showResponse(response);
    },

    ResetViews: function(response) {
        if (response[0]['type'] == 'response_notice') {
            getDG('reports_datagrid');
        }
        showResponse(response);
    },

    ResetClicks: function(response) {
        if (response[0]['type'] == 'response_notice') {
            getDG('reports_datagrid');
        }
        showResponse(response);
    },

    InsertGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            getGroups();
            stopAction();
        }
        showResponse(response);
    },

    UpdateGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            getGroups();
            stopAction();
        }
        showResponse(response);
    },

    DeleteGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            getGroups();
            stopAction();
        }
        showResponse(response);
    },

    AddBannersToGroup: function(response) {
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
    var banners = BannerAjax.callSync('getBannersDataGrid', name, offset, $('bgroup_filter').value);
    if (reset) {
        stopAction();
        $(name).setCurrentPage(0);
        var total = BannerAjax.callSync('GetBannersCount', $('bgroup_filter').value);
    }

    resetGrid(name, banners, total);
}

function makeBigBannerEntry()
{
    var height = parseInt($('banner').style.height.substr(0, $('banner').style.height.length-2));
    height += 10;
    $('banner').style.height = height + 'px';
}

function setTemplate(template)
{
    $('template').value = template;
    $('template').focus();
}

function changeThroughUpload(checked) {
    if (checked) {
        $('banner').style.display = 'none';
        $('upload_banner').style.display = 'inline';
    } else {
        $('upload_banner').style.display = 'none';
        $('banner').style.display = 'inline';
    }
}

/**
 * Get groups list
 */
function getGroups()
{
    resetCombo($('groups_combo'));
    var groupList = BannerAjax.callSync('GetGroups', -1, -1);
    if (groupList != false) {
        var combo = $('groups_combo');
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
    if ($('title').value.blank() ||
        $('template').value.blank() ||
        $('gid').value == 0)
    {
        alert(incompleteBannerFields);
        return false;
    }

    if (document.getElementsByName('through_upload[]').item(0).checked) {
        can_submit = true;
        document.banner_info.submit();
        return true;
    } else {
        if ($('bid').value == 0) {
            BannerAjax.callAsync('InsertBanner',
                                 $('title').value,
                                 $('url').value,
                                 $('gid').value,
                                 $('banner').value,
                                 $('template').value,
                                 $('views_limit').value,
                                 $('clicks_limit').value,
                                 $('start_time').value,
                                 $('stop_time').value,
                                 $('random').value,
                                 $('published').value);
        } else {
            BannerAjax.callAsync('UpdateBanner',
                                $('bid').value,
                                $('title').value,
                                $('url').value,
                                $('gid').value,
                                $('banner').value,
                                $('template').value,
                                $('views_limit').value,
                                $('clicks_limit').value,
                                $('start_time').value,
                                $('stop_time').value,
                                $('random').value,
                                $('published').value);
        }
    }
}

/**
 * Saves data / changes on the group's form
 */
function saveGroup()
{
    if (currentAction == 'ManageGroupBanners') {
        var box  = $('group_members');
        var keys = new Array();
        for(var i = 0; i < box.length; i++) {
            keys[i] = box.options[i].value;
        }
        BannerAjax.callAsync('AddBannersToGroup', selectedGroup, keys);
    } else {
        if ($('title').value.blank()) {
            alert(incompleteGroupFields);
            return false;
        }

        if (selectedGroup == null) {
            $('gid').value = 0;
            BannerAjax.callAsync(
                            'InsertGroup',
                            $('title').value,
                            $('count').value,
                            $('show_title').value,
                            $('show_type').value,
                            $('published').value);
        } else {
            $('gid').value = selectedGroup;
            BannerAjax.callAsync(
                            'UpdateGroup',
                            $('gid').value,
                            $('title').value,
                            $('count').value,
                            $('show_title').value,
                            $('show_type').value,
                            $('published').value);
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
        BannerAjax.callAsync('DeleteBanner', bid);
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
        BannerAjax.callAsync('ResetViews', bid);
    }
}

/**
 * Reset Clicks Counter
 */
function resetClicks(bid)
{
    var answer = confirm(confirmResetBannerClicks);
    if (answer) {
        BannerAjax.callAsync('ResetClicks', bid);
    }
}

/**
 * Delete group
 */
function deleteGroup()
{
    var answer = confirm(confirmGroupDelete);
    if (answer) {
        BannerAjax.callAsync('DeleteGroup', selectedGroup);
    }
}

/**
 * Add group
 */
function addGroup()
{
    if (cacheMasterForm == null) {
        cacheMasterForm = BannerAjax.callSync('GetGroupUI');
    }
    currentAction = 'AddGroup';

    $('cancel_action').style.display = 'inline';
    $('save_group').style.display = 'inline';
    $('add_banners').style.display = 'none';
    $('add_group').style.display = 'none';
    $('group_area').innerHTML = cacheMasterForm;
    selectedGroup = null;
}

/**
 * Edit banner
 */
function editBanner(element, bid)
{
    if (bid == 0) return;
    currentAction = 'Banners';
    $('legend_title').innerHTML = editBanner_title;

    selectDataGridRow(element.parentNode.parentNode);

    var banner = BannerAjax.callSync('GetBanner', bid);
    $('bid').value    = banner['id'];
    $('title').value  = banner['title'].defilter();
    $('url').value    = banner['url'];
    $('gid').value    = banner['gid'];
    document.getElementsByName('through_upload[]').item(0).checked = false;
    $('banner').value       = banner['banner'].defilter();
    defaultTemplate         = banner['template'];
    $('template').value     = defaultTemplate;
    defaultTemplate = banner['template'];
    $('views_limit').value  = banner['views_limitation'];
    $('clicks_limit').value = banner['clicks_limitation'];
    if (banner['start_time'] == null) banner['start_time'] = '';
    if (banner['stop_time']  == null) banner['stop_time']  = '';
    $('start_time').value   = banner['start_time'];
    $('stop_time').value    = banner['stop_time'];
    $('random').selectedIndex  = banner['random'];
    $('published').selectedIndex = banner['published']? 1 : 0;
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    if (cacheMasterForm == null) {
        cacheMasterForm = BannerAjax.callSync('GetGroupUI');
    }

    $('group_banners_area').innerHTML = '';
    currentAction = 'EditGroup';
    $('cancel_action').style.display = 'inline';
    $('save_group').style.display = 'inline';
    $('add_banners').style.display = 'inline';
    $('delete_group').style.display = 'inline';
    $('add_group').style.display = 'none';
    $('group_area').innerHTML = cacheMasterForm;
    selectedGroup = gid;
    var groupInfo = BannerAjax.callSync('GetGroup', selectedGroup);
    $('gid').value   = groupInfo['id'];
    $('title').value = groupInfo['title'].defilter();
    $('count').value = groupInfo['limit_count'];
    $('show_title').value = groupInfo['show_title']? 1 : 0;
    $('show_type').value  = groupInfo['show_type'];
    $('published').selectedIndex = groupInfo['published']? 1 : 0;
}

/**
 *
 */
function AddableBanner()
{
    var banners = $('banners_combo');
    if (banners.selectedIndex == -1) return false;

    var box = $('group_members');
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
    var banners = $('banners_combo');
    var title = banners.options[banners.selectedIndex].text;
    var value = banners.options[banners.selectedIndex].value;
    var box = $('group_members');
    box.options[box.options.length] = new Option(title, value);
}

/**
 *
 */
function delBannerFromList()
{
    var box = $('group_members');
    if (box.selectedIndex != -1) {
        box.options[box.selectedIndex] = null;
    }
}

/**
 *
 */
function upBannerRank()
{
    var box = $('group_members');
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
    var box = $('group_members');
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
        $('legend_title').innerHTML = addBanner_title;
        defaultTemplate         = '';
        $('bid').value          = 0;
        $('title').value        = '';
        $('url').value          = 'http://';
        $('banner').value       = '';
        $('upload_banner').value= '';
        $('template').value     = '';
        $('views_limit').value  = '';
        $('clicks_limit').value = '';
        $('start_time').value   = '';
        $('stop_time').value    = '';
        $('random').value       = 0;
        $('published').value    = 1;
        unselectDataGridRow();
        break;
    case 'EditGroup':
    case 'AddGroup':
        $('add_group').style.display = 'inline';
        $('save_group').style.display = 'none';
        $('add_banners').style.display = 'none';
        $('delete_group').style.display = 'none';
        $('groups_combo').selectedIndex = -1;
        $('group_area').innerHTML = '';
        $('cancel_action').style.display = 'none';
        selectedGroup = null;
        break;
    case 'ManageGroupBanners':
        currentAction = 'EditGroup';
        $('cancel_action').style.display = 'inline';
        $('save_group').style.display = 'inline';
        $('add_banners').style.display = 'inline';
        $('delete_group').style.display = 'inline';
        $('add_group').style.display = 'none';
        $('group_banners_area').innerHTML = '';
        //--
        $('title').disabled   = false;
        $('count').disabled   = false;
        $('published').disabled = false;
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
        cacheSlaveForm = BannerAjax.callSync('GetGroupBannersUI');
    }
    $('save_group').style.display = 'inline';
    $('add_banners').style.display = 'none';
    $('delete_group').style.display = 'none';
    $('group_banners_area').innerHTML = cacheSlaveForm;
    //--
    $('title').disabled   = true;
    $('count').disabled   = true;
    $('published').disabled = true;
    //--
    currentAction = 'ManageGroupBanners';
    var banners = BannerAjax.callSync('GetBanners', -1, selectedGroup);
    var box = $('group_members');
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