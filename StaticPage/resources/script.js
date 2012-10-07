/**
 * StaticPage Javascript actions
 *
 * @category   Ajax
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Use async mode, create Callback
 */
var StaticPageCallback = { 
    deletepage: function(response) {
        if (response[0]['css'] == 'notice-message') {
            getDG('pages_datagrid');
        }
        showResponse(response);
    }, 

    deletetranslation: function(response) {
        if (response[0]['css'] == 'notice-message') {
            getDG('pages_datagrid');
        }
        showResponse(response);
    }, 
    
    massivedelete: function(response) {
        if (response[0]['css'] == 'notice-message') {
            var rows = $('pages_datagrid').getSelectedRows();
            if (rows.length > 0) {
                for(var i=0; i<rows.length; i++) {
                    $('pages_datagrid').deleteItem();
                }
            }
            PiwiGrid.multiSelect($('pages_datagrid'));
            getDG('pages_datagrid');
        }
        showResponse(response);      
    },
    
    updatesettings: function(response) {
        showResponse(response);
    }, 

    autodraft: function(response) {
        showSimpleResponse(response);
    },

    insertgroup: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            $('groups_datagrid').addItem();
            $('groups_datagrid').setCurrentPage(0);
            getDG('groups_datagrid');
        }
        showResponse(response);
    },

    updategroup: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG('groups_datagrid');
        }
        showResponse(response);
    },

    deletegroup: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            $('groups_datagrid').deleteItem();
            getDG('groups_datagrid');
        }
        showResponse(response);
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
    var title     = document.forms[0].elements['title'].value;
    var group     = document.forms[0].elements['group_id'].value;
    var fasturl   = document.forms[0].elements['fast_url'].value;
    var language  = document.forms[0].elements['language'].value;
    var published = document.forms[0].elements['published'].value;
    var showtitle = document.forms[0].elements['show_title'].value;
    var actioni   = document.forms[0].elements['action'].value;
    var id        = '';

    switch (actioni) {
        case 'AddPage':
            id = 'NEW';
            break;
        case 'SaveEditPage':
            id = document.forms[0].elements['id'].value;
            break;
    }
    var content   = getEditorValue('content');

    staticpage.autodraft(id, group, fasturl, showtitle, title, content, language, published);
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
    var title   = form.elements['title'].value;
    var content = getEditorValue('content');

    content = staticpageSync.parsetext(content);
    
    var preview = document.getElementById('preview');
    preview.style.display = 'block';

    var titlePreview   = document.getElementById('previewTitle');
    var contentPreview = document.getElementById('previewContent');

    titlePreview.innerHTML   = title;
    contentPreview.innerHTML = content;    
}

/**
 * Delete a page : function
 */
function deletePage(id, redirect)
{
    var confirmation = confirm(confirmPageDelete);
    if (confirmation) {
        if (redirect) {
            var response = staticpageSync.deletepage(id);
            showResponse(response);
            if (response[0]['css'] == 'notice-message') {
                window.location= base_script + '?gadget=StaticPage&action=Admin';
            }
        } else {
            staticpage.deletepage(id);
        }
    }
}

/**
 * Delete a translated page : function
 */
function deleteTranslation(id, redirect)
{
    var confirmation = confirm(confirmPageDelete);
    if (confirmation) {
        if (redirect) {
            var response = staticpageSync.deletetranslation(id);
            showResponse(response);
            if (response[0]['css'] == 'notice-message') {
                window.location= base_script + '?gadget=StaticPage&action=Admin';
            }
        } else {
            staticpage.deletetranslation(id);
        }
    }
}

/**
 * Can use massive delete?
 */
function massiveDelete() 
{
    var rows = $('pages_datagrid').getSelectedRows();
    if (rows.length > 0) {
        var confirmation = confirm(confirmMassiveDelete);
        if (confirmation) {
            staticpage.massivedelete(rows);
        }
    }
}

/**
 * Save settings
 */
function updateSettings()
{
    var defaultPage = $('default_page').value;
    var multiLang   = $('multilanguage').value;
    staticpage.updatesettings(defaultPage, multiLang);
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
    var result = staticpageSync.searchpages($('group').value,
                                            $('status').value,
                                            $('search').value,
                                            offset);
    if (reset) {
        var total = staticpageSync.sizeofsearch($('group').value,
                                                $('status').value,
                                                $('search').value);
    }
    resetGrid(name, result, total);
}

/**
 * Fetches groups data and fills the grid
 */
function getPagesGroups(name, offset, reset)
{
    var groups = staticpageSync.getgroupsgrid(offset);
    if (reset) {
        var total = staticpageSync.getgroupscount();
    }

    resetGrid(name, groups, total);
}

/**
 * Updates a group
 */
function editGroup(rowElement, gid)
{
    selectedGroup = gid;
    selectGridRow('groups_datagrid', rowElement.parentNode.parentNode);
    $('legend_title').update(edit_group_title);
    var group = staticpageSync.getgroup(selectedGroup);
    $('title').value    = group['title'];
    $('fast_url').value = group['fast_url'];
    $('visible').value  = group['visible'];
    $('title').focus();
}

/**
 * save a category
 */
function saveGroup()
{
    if ($('title').value.blank()) {
        alert(incomplete_fields);
        $('title').focus();
        return false;
    }

    if (selectedGroup == 0) {
        staticpage.insertgroup($('title').value,
                               $('fast_url').value,
                               $('visible').value);
    } else {
        staticpage.updategroup(selectedGroup,
                               $('title').value,
                               $('fast_url').value,
                               $('visible').value);
    }
}

/**
 * Deletes a group
 */
function deleteGroup(rowElement, gid)
{
    selectGridRow('groups_datagrid', rowElement.parentNode.parentNode);
    if (confirm(confirm_group_delete)) {
        staticpage.deletegroup(gid);
    }

    stopAction();
}

/**
 * Show the response but only text, nothing with datagrid.
 * FIXME!
 */
function showSimpleResponse(message)
{
    if (!autoDraftDone) {
        var actioni   = document.forms[0].elements['action'].value;
        if (actioni == 'AddPage' && message[0]['css'] == 'notice-message') {
            document.forms[0].elements['action'].value = 'SaveEditPage';
            document.forms[0].elements['id'].value     = message[0]['message']['id'];
            message[0]['message'] = message[0]['message']['message'];
        }
        autoDraftDone = true;
    }
    showResponse(message);
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    selectedGroup = 0;
    $('legend_title').update(add_group_title);
    $('title').value    = '';
    $('fast_url').value = '';
    $('visible').value  = 'true';
    unselectGridRow('groups_datagrid');
    $('title').focus();
}

var staticpage = new staticpageadminajax(StaticPageCallback);
staticpage.serverErrorFunc = Jaws_Ajax_ServerError;
staticpage.onInit = showWorkingNotification;
staticpage.onComplete = hideWorkingNotification;

var staticpageSync = new staticpageadminajax();
staticpageSync.serverErrorFunc = Jaws_Ajax_ServerError;
staticpageSync.onInit = showWorkingNotification;
staticpageSync.onComplete = hideWorkingNotification;

selectedGroup = 0;
var autoDraftDone = false;
