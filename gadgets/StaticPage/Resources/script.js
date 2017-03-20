/**
 * StaticPage Javascript actions
 *
 * @category   Ajax
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Use async mode, create Callback
 */
var StaticPageCallback = {
    DeletePage: function(response) {
        if (response[0]['type'] == 'alert-success') {
            getDG('pages_datagrid');
        }
        StaticPageAjax.showResponse(response);
    },

    DeleteTranslation: function(response) {
        if (response[0]['type'] == 'alert-success') {
            getDG('pages_datagrid');
        }
        StaticPageAjax.showResponse(response);
    },

    MassiveDelete: function(response) {
        if (response[0]['type'] == 'alert-success') {
            var rows = $('#pages_datagrid')[0].getSelectedRows();
            if (rows.length > 0) {
                for(var i=0; i<rows.length; i++) {
                    $('#pages_datagrid')[0].deleteItem();
                }
            }
            PiwiGrid.multiSelect($('#pages_datagrid'));
            getDG('pages_datagrid');
        }
        StaticPageAjax.showResponse(response);
    },

    UpdateSettings: function(response) {
        StaticPageAjax.showResponse(response);
    },

    AutoDraft: function(response) {
        showSimpleResponse(response);
    },

    InsertGroup: function(response) {
        if (response[0]['type'] == 'alert-success') {
            stopAction();
            $('#groups_datagrid')[0].addItem();
            $('#groups_datagrid')[0].setCurrentPage(0);
            getDG('groups_datagrid');
        }
        StaticPageAjax.showResponse(response);
    },

    UpdateGroup: function(response) {
        if (response[0]['type'] == 'alert-success') {
            stopAction();
            getDG('groups_datagrid');
        }
        StaticPageAjax.showResponse(response);
    },

    DeleteGroup: function(response) {
        if (response[0]['type'] == 'alert-success') {
            stopAction();
            $('#groups_datagrid')[0].deleteItem();
            getDG('groups_datagrid');
        }
        StaticPageAjax.showResponse(response);
    }

};

// {{{ Function AutoDraft
/**
 * This function is the main idea behind the auto drafting
 * it will get the values of the fields on the form and then
 * pass them to the function AutoDraft in StaticPageAjax.php
 * and also output a nice message at the end :-)
 */
function AutoDraft()
{
    // FIXME: temporary disable auto draft
    return;
    var title     = document.forms[0]['title'].value;
    var group     = document.forms[0]['group_id'].value;
    var fasturl   = document.forms[0]['fast_url'].value;
    var language  = document.forms[0]['language'].value;
    var published = document.forms[0]['published'].value;
    var tags      = document.forms[0]['tags'].value;
    var showtitle = document.forms[0]['show_title'].value;
    var actioni   = document.forms[0]['action'].value;
    var id        = '';

    switch (actioni) {
        case 'AddPage':
            id = 'NEW';
            break;
        case 'SaveEditPage':
            id = document.forms[0]['id'].value;
            break;
    }
    var content = $('#content').val();

    StaticPageAjax.callAsync(
        'AutoDraft',
        [id, group, fasturl, showtitle, title, content, language, tags, published]
    );
    setTimeout('startAutoDrafting();', 120000);
}

// }}}
// {{{ Function startAutoDrafting
/**
 * Just the mother function that will make sure that auto drafting is running
 * and is being run every ~ 120 seconds (2 minutes).
 *
 * @see AutoDraft();
 */
function startAutoDrafting()
{
    AutoDraft();
}

// }}}
/**
 * Prepare the preview
 */
function parseText(form)
{
    var title   = form['title'].value;
    var content = $('#content').val();

    content = StaticPageAjax.callSync('ParseText', content);
    $('#preview').css('display', 'table');

    $('#previewTitle').html(title);
    $('#previewContent').html(content);
}

/**
 * Delete a page : function
 */
function deletePage(id, redirect)
{
    var confirmation = confirm(jaws.StaticPage.Defines.confirmPageDelete);
    if (confirmation) {
        if (redirect) {
            var response = StaticPageAjax.callSync('DeletePage', id);
            StaticPageAjax.showResponse(response);
            if (response[0]['type'] == 'alert-success') {
                window.location = StaticPageAjax.baseScript + '?gadget=StaticPage';
            }
        } else {
            StaticPageAjax.callAsync('DeletePage', id);
        }
    }
}

/**
 * Delete a translated page : function
 */
function deleteTranslation(id, redirect)
{
    var confirmation = confirm(jaws.StaticPage.Defines.confirmPageDelete);
    if (confirmation) {
        if (redirect) {
            var response = StaticPageAjax.callSync('DeleteTranslation', id);
            StaticPageAjax.showResponse(response);
            if (response[0]['type'] == 'alert-success') {
                window.location = StaticPageAjax.baseScript + '?gadget=StaticPage';
            }
        } else {
            StaticPageAjax.callAsync('DeleteTranslation', id);
        }
    }
}

/**
 * Can use massive delete?
 */
function massiveDelete()
{
    var rows = $('#pages_datagrid')[0].getSelectedRows();
    if (rows.length > 0) {
        var confirmation = confirm(jaws.StaticPage.Defines.confirmMassiveDelete);
        if (confirmation) {
            StaticPageAjax.callAsync('MassiveDelete', rows);
        }
    }
}

/**
 * Save settings
 */
function updateSettings()
{
    var settings = [$('#default_page').val(), $('#multilanguage').val()];
    StaticPageAjax.callAsync('UpdateSettings', settings);
}

/**
 * Search for pages and translations
 */
function searchPage()
{
    getDG('pages_datagrid', 0, true);
}

/**
 * Get pages data
 */
function getPages(name, offset, reset)
{
    var result = StaticPageAjax.callSync(
        'SearchPages', [
            $('#group').val(),
            $('#status').val(),
            $('#search').val(),
            $('#orderby').val(),
            offset
        ]
    );
    if (reset) {
        var total = StaticPageAjax.callSync(
            'SizeOfSearch', [
                $('#group').val(),
                $('#status').val(),
                $('#search').val()
            ]
        );
    }
    resetGrid(name, result, total);
}

/**
 * Fetches groups data and fills the grid
 */
function getPagesGroups(name, offset, reset)
{
    var groups = StaticPageAjax.callSync('GetGroupsGrid', offset);
    if (reset) {
        var total = StaticPageAjax.callSync('GetGroupsCount');
    }

    resetGrid(name, groups, total);
}

/**
 * Initiates form with group data
 */
function editGroup(rowElement, gid)
{
    selectedGroup = gid;
    selectGridRow('groups_datagrid', rowElement.parentNode.parentNode);
    $('#legend_title').html(jaws.StaticPage.Defines.edit_group_title);
    var group = StaticPageAjax.callSync('GetGroup', selectedGroup);
    $('#title').val(group['title'].defilter())[0].focus();
    $('#meta_keys').val(group['meta_keywords'].defilter());
    $('#meta_desc').val(group['meta_description'].defilter());
    $('#fast_url').val(group['fast_url']);
    $('#visible')[0].value = group['visible'];
}

/**
 * Updates the group
 */
function saveGroup()
{
    if (!$('#title').val()) {
        alert(jaws.StaticPage.Defines.incomplete_fields);
        $('#title')[0].focus();
        return false;
    }

    if (selectedGroup == 0) {
        StaticPageAjax.callAsync(
            'InsertGroup', [
                $('#title').val(),
                $('#fast_url').val(),
                $('#meta_keys').val(),
                $('#meta_desc').val(),
                $('#visible').val()
            ]
        );
    } else {
        StaticPageAjax.callAsync(
            'UpdateGroup', [
                selectedGroup,
                $('#title').val(),
                $('#fast_url').val(),
                $('#meta_keys').val(),
                $('#meta_desc').val(),
                $('#visible').val()
            ]
        );
    }
}

/**
 * Deletes a group
 */
function deleteGroup(rowElement, gid)
{
    selectGridRow('groups_datagrid', rowElement.parentNode.parentNode);
    if (confirm(jaws.StaticPage.Defines.confirm_group_delete)) {
        StaticPageAjax.callAsync('DeleteGroup', gid);
    }

    stopAction();
}

/**
 * Show the response but only text, nothing with datagrid.
 * FIXME!
 */
function showSimpleResponse(response)
{
    if (!autoDraftDone) {
        var action = document.forms[0]['action'].value;
        if (action == 'AddPage' && response[0]['type'] == 'alert-success') {
            document.forms[0]['action'].value = 'SaveEditPage';
            document.forms[0]['id'].value = response[0]['data'];
        }
        autoDraftDone = true;
    }
    StaticPageAjax.showResponse(response);
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    selectedGroup = 0;
    $('#legend_title').html(jaws.StaticPage.Defines.add_group_title);
    $('#title').val('');
    $('#fast_url').val('');
    $('#meta_keys').val('');
    $('#meta_desc').val('');
    $('#visible').val('true');
    unselectGridRow('groups_datagrid');
    $('#title')[0].focus();
}

$(document).ready(function() {
    switch (jaws.Defines.mainAction) {
        case 'ManagePages':
            initDataGrid('pages_datagrid', StaticPageAjax, 'getPages');
            break;

        case 'AddNewPage':
            break;

        case 'Groups':
            stopAction();
            initDataGrid('groups_datagrid', StaticPageAjax, 'getPagesGroups');
            break;
    }
});

var StaticPageAjax = new JawsAjax('StaticPage', StaticPageCallback);

selectedGroup = 0;
var autoDraftDone = false;
