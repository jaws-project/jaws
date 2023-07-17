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
        if (response['type'] == 'alert-success') {
            stopAction();
            $('#banners_datagrid')[0].addItem();
            $('#banners_datagrid')[0].setCurrentPage(0);
            getDG('banners_datagrid');
        }
    },

    UpdateBanner: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
            getDG('banners_datagrid');
        }
    },

    DeleteBanner: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
            $('#banners_datagrid')[0].deleteItem();
            getDG('banners_datagrid');
        }
    },

    ResetViews: function(response) {
        if (response['type'] == 'alert-success') {
            getDG('reports_datagrid');
        }
    },

    ResetClicks: function(response) {
        if (response['type'] == 'alert-success') {
            getDG('reports_datagrid');
        }
    },

    InsertGroup: function(response) {
        if (response['type'] == 'alert-success') {
            getGroups();
            stopAction();
        }
    },

    UpdateGroup: function(response) {
        if (response['type'] == 'alert-success') {
            getGroups();
            stopAction();
        }
    },

    DeleteGroup: function(response) {
        if (response['type'] == 'alert-success') {
            getGroups();
            stopAction();
        }
    },

    AddBannersToGroup: function(response) {
        //
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
    var banners = BannerAjax.call(
        'getBannersDataGrid',
        [name, offset, $('#bgroup_filter').val(), $('#domain_filter').val()],
        false, {'async': false}
    );
    if (reset) {
        stopAction();
        $('#'+name)[0].setCurrentPage(0);
        var total = BannerAjax.call(
            'GetBannersCount',
            [$('#bgroup_filter').val(), $('#domain_filter').val()],
            false, {'async': false}
        );
    }

    resetGrid(name, banners, total);
}

function makeBigBannerEntry()
{
    var height = $('#banner').height() + 10;
    $('#banner').css('height', height + 'px');
}

function setTemplate(template)
{
    $('#template').val(template);
    $('#template').focus();
}

function changeThroughUpload(checked) {
    if (checked) {
        $('#banner').css('display', 'none');
        $('#upload_banner').css('display', 'inline');
    } else {
        $('#upload_banner').css('display', 'none');
        $('#banner').css('display', 'inline');
    }
}

/**
 * Get groups list
 */
function getGroups()
{
    resetCombo($('#groups_combo')[0]);
    var groupList = BannerAjax.call('GetGroups', [-1, -1], false, {'async': false});
    if (groupList != false) {
        var combo = $('#groups_combo')[0];
        var i = 0;
        $.each(groupList, function(index, value) {
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
    if (!$('#title').val() ||
        !$('#template').val() ||
        $('#gid').val() == 0)
    {
        alert(Jaws.gadgets.Banner.defines.incompleteBannerFields);
        return false;
    }

    if (document.getElementsByName('through_upload[]').item(0).checked) {
        can_submit = true;
        document.banner_info.submit();
        return true;
    } else {
        if ($('#bid').val() == 0) {
            BannerAjax.call(
                'InsertBanner', [
                    $('#domain').val(),
                    $('#title').val(),
                    $('#url').val(),
                    $('#gid').val(),
                    $('#banner').val(),
                    $('#template').val(),
                    $('#views_limit').val(),
                    $('#clicks_limit').val(),
                    $('#datepicker_start_time_input').val(),
                    $('#datepicker_stop_time_input').val(),
                    $('#random').val(),
                    $('#published').val()
                ]
            );
        } else {
            BannerAjax.call(
                'UpdateBanner', [
                    $('#bid').val(),
                    $('#domain').val(),
                    $('#title').val(),
                    $('#url').val(),
                    $('#gid').val(),
                    $('#banner').val(),
                    $('#template').val(),
                    $('#views_limit').val(),
                    $('#clicks_limit').val(),
                    $('#datepicker_start_time_input').val(),
                    $('#datepicker_stop_time_input').val(),
                    $('#random').val(),
                    $('#published').val()
                ]
            );
        }
    }
}

/**
 * Saves data / changes on the group's form
 */
function saveGroup()
{
    if (currentAction == 'ManageGroupBanners') {
        var box  = $('#group_members')[0];
        var keys = new Array();
        for(var i = 0; i < box.length; i++) {
            keys[i] = box.options[i].value;
        }
        BannerAjax.call('AddBannersToGroup', [selectedGroup, keys]);
    } else {
        if (!$('#title').val()) {
            alert(Jaws.gadgets.Banner.defines.incompleteGroupFields);
            return false;
        }

        if (selectedGroup == null) {
            $('#gid').val(0);
            BannerAjax.call(
                'InsertGroup',[
                    $('#title').val(),
                    $('#count').val(),
                    $('#show_title').val(),
                    $('#show_type').val(),
                    $('#published').val()
                ]
            );
        } else {
            $('#gid').val(selectedGroup);
            BannerAjax.call(
                'UpdateGroup', [
                    $('#gid').val(),
                    $('#title').val(),
                    $('#count').val(),
                    $('#show_title').val(),
                    $('#show_type').val(),
                    $('#published').val()
                ]
            );
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
    var answer = confirm(Jaws.gadgets.Banner.defines.confirmBannerDelete);
    if (answer) {
        BannerAjax.call('DeleteBanner', bid);
    }
    unselectDataGridRow();
}

/**
 * Reset Views Counter
 */
function resetViews(bid)
{
    var answer = confirm(Jaws.gadgets.Banner.defines.confirmResetBannerViews);
    if (answer) {
        BannerAjax.call('ResetViews', bid);
    }
}

/**
 * Reset Clicks Counter
 */
function resetClicks(bid)
{
    var answer = confirm(Jaws.gadgets.Banner.defines.confirmResetBannerClicks);
    if (answer) {
        BannerAjax.call('ResetClicks', bid);
    }
}

/**
 * Delete group
 */
function deleteGroup()
{
    var answer = confirm(Jaws.gadgets.Banner.defines.confirmGroupDelete);
    if (answer) {
        BannerAjax.call('DeleteGroup', selectedGroup);
    }
}

/**
 * Add group
 */
function addGroup()
{
    if (cacheMasterForm == null) {
        cacheMasterForm = BannerAjax.call('GetGroupUI', {}, false, {'async': false});
    }
    currentAction = 'AddGroup';

    $('#cancel_action').css('display', 'inline');
    $('#save_group').css('display', 'inline');
    $('#add_banners').css('display', 'none');
    $('#add_group').css('display', 'none');
    $('#group_area').html(cacheMasterForm);
    selectedGroup = null;
}

/**
 * Edit banner
 */
function editBanner(element, bid)
{
    if (bid == 0) return;
    currentAction = 'Banners';
    $('#legend_title').html(Jaws.gadgets.Banner.defines.editBanner_title);

    selectDataGridRow(element.parentNode.parentNode);

    var banner = BannerAjax.call('GetBanner', bid, false, {'async': false});
    $('#bid').val(banner['id']);
    $('#domain').val(banner['domain']);
    $('#title').val(banner['title'].defilter());
    $('#url').val(banner['url']);
    $('#gid').val(banner['gid']);
    document.getElementsByName('through_upload[]').item(0).checked = false;
    $('#banner').val(banner['banner'].defilter());
    defaultTemplate = banner['template'];
    $('#template').val(defaultTemplate);
    defaultTemplate = banner['template'];
    $('#views_limit').val(banner['views_limitation']);
    $('#clicks_limit').val(banner['clicks_limitation']);
    if (banner['start_time'] == null) banner['start_time'] = '';
    if (banner['stop_time']  == null) banner['stop_time']  = '';
    $('#datepicker_start_time_input').val(banner['start_time']);
    $('#datepicker_stop_time_input').val(banner['stop_time']);
    $('#random').prop('selectedIndex', banner['random']);
    $('#published').prop('selectedIndex', banner['published']? 1 : 0);
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    if (cacheMasterForm == null) {
        cacheMasterForm = BannerAjax.call('GetGroupUI', {}, false, {'async': false});
    }

    $('#group_banners_area').html('');
    currentAction = 'EditGroup';
    $('#cancel_action').css('display', 'inline');
    $('#save_group').css('display', 'inline');
    $('#add_banners').css('display', 'inline');
    $('#delete_group').css('display', 'inline');
    $('#add_group').css('display', 'none');
    $('#group_area').html(cacheMasterForm);
    selectedGroup = gid;
    var groupInfo = BannerAjax.call('GetGroup', selectedGroup, false, {'async': false});
    $('#gid').val(groupInfo['id']);
    $('#title').val(groupInfo['title'].defilter());
    $('#count').val(groupInfo['limit_count']);
    $('#show_title').val(groupInfo['show_title']? 1 : 0);
    $('#show_type').val(groupInfo['show_type']);
    $('#published').prop('selectedIndex', groupInfo['published']? 1 : 0);
}

/**
 *
 */
function AddableBanner()
{
    var banners = $('#banners_combo')[0];
    if (banners.selectedIndex == -1) return false;

    var box = $('#group_members')[0];
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
    var banners = $('#banners_combo')[0];
    var title = banners.options[banners.selectedIndex].text;
    var value = banners.options[banners.selectedIndex].value;
    var box = $('#group_members')[0];
    box.options[box.options.length] = new Option(title, value);
}

/**
 *
 */
function delBannerFromList()
{
    var box = $('#group_members')[0];
    if (box.selectedIndex != -1) {
        box.options[box.selectedIndex] = null;
    }
}

/**
 *
 */
function upBannerRank()
{
    var box = $('#group_members')[0];
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
    var box = $('#group_members')[0];
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
        $('#legend_title').html(Jaws.gadgets.Banner.defines.addBanner_title);
        defaultTemplate = '';
        $('#bid').val(0);
        $('#title').val('');
        $('#url').val('http://');
        $('#banner').val('');
        $('#upload_banner').val('');
        $('#template').val('');
        $('#views_limit').val('');
        $('#clicks_limit').val('');
        $('#datepicker_start_time_input').val('');
        $('#datepicker_stop_time_input').val('');
        $('#random').val(0);
        $('#published').val(1);
        unselectDataGridRow();
        break;
    case 'EditGroup':
    case 'AddGroup':
        $('#add_group').css('display', 'inline');
        $('#save_group').css('display', 'none');
        $('#add_banners').css('display', 'none');
        $('#delete_group').css('display', 'none');
        $('#groups_combo').prop('selectedIndex', -1);
        $('#group_area').html('');
        $('#cancel_action').css('display', 'none');
        selectedGroup = null;
        break;
    case 'ManageGroupBanners':
        currentAction = 'EditGroup';
        $('#cancel_action').css('display', 'inline');
        $('#save_group').css('display', 'inline');
        $('#add_banners').css('display', 'inline');
        $('#delete_group').css('display', 'inline');
        $('#add_group').css('display', 'none');
        $('#group_banners_area').html('');
        //--
        $('#title').prop('disabled', false);
        $('#count').prop('disabled', false);
        $('#published').prop('disabled', false);
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
        cacheSlaveForm = BannerAjax.call('GetGroupBannersUI', {}, false, {'async': false});
    }
    $('#save_group').css('display', 'inline');
    $('#add_banners').css('display', 'none');
    $('#delete_group').css('display', 'none');
    $('#group_banners_area').html(cacheSlaveForm);
    //--
    $('#title').prop('disabled', true);
    $('#count').prop('disabled', true);
    $('#published').prop('disabled', true);
    //--
    currentAction = 'ManageGroupBanners';
    var banners = BannerAjax.call('GetBanners', [-1, selectedGroup], false, {'async': false});
    var box = $('#group_members')[0];
    box.length = 0;
    for(var i = 0; i < banners.length; i++) {
        box.options[i] = new Option(banners[i]['title'] +' '+'('+banners[i]['url']+')', banners[i]['id']);
    }
}

$(document).ready(function() {
    switch (Jaws.defines.mainAction) {
        case 'Banners':
            currentAction = 'Banners';
            $('#bgroup_filter').prop('selectedIndex', 0);
            initDataGrid('banners_datagrid', BannerAjax, getBannersDataGrid);
            break;

        case 'Groups':
            $('#groups_combo').selectedIndex = -1;
            break;

        case 'Reports':
            $('#bgroup_filter').selectedIndex = 0;
            initDataGrid('reports_datagrid', BannerAjax, getBannersDataGrid);
            break;
    }
});

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

//Which action are we running?
var currentAction = null;

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;

var defaultTemplate        = "";