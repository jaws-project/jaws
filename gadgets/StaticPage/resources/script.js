/**
 * StaticPage Javascript actions
 *
 * @category   Ajax
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
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

    StaticPageAjax.callAsync('autodraft', id, group, fasturl, showtitle, title, content, language, published);
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

    content = StaticPageAjax.callSync('parsetext', content);
    $('preview').style.display   = 'table';

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
            var response = StaticPageAjax.callSync('deletepage', id);
            showResponse(response);
            if (response[0]['css'] == 'notice-message') {
                window.location= base_script + '?gadget=StaticPage&action=Admin';
            }
        } else {
            StaticPageAjax.callAsync('deletepage', id);
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
            var response = StaticPageAjax.callSync('deletetranslation', id);
            showResponse(response);
            if (response[0]['css'] == 'notice-message') {
                window.location= base_script + '?gadget=StaticPage&action=Admin';
            }
        } else {
            StaticPageAjax.callAsync('deletetranslation', id);
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
            StaticPageAjax.callAsync('massivedelete', rows);
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
    StaticPageAjax.callAsync('updatesettings', defaultPage, multiLang);
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
        'searchpages',
        $('group').value,
        $('status').value,
        $('search').value,
        $('orderby').value,
        offset
    );
    if (reset) {
        var total = StaticPageAjax.callSync(
            'sizeofsearch',
            $('group').value,
            $('status').value,
            $('search').value
        );
    }
    resetGrid(name, result, total);
}

/**
 * Fetches groups data and fills the grid
 */
function getPagesGroups(name, offset, reset)
{
    var groups = StaticPageAjax.callSync('getgroupsgrid', offset);
    if (reset) {
        var total = StaticPageAjax.callSync('getgroupscount');
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
    $('legend_title').set('html', edit_group_title);
    var group = StaticPageAjax.callSync('getgroup', selectedGroup);
    $('title').value     = group['title'].defilter();
    $('meta_keys').value = group['meta_keywords'].defilter();
    $('meta_desc').value = group['meta_description'].defilter();
    $('fast_url').value  = group['fast_url'];
    $('visible').value   = group['visible'];
    $('title').focus();
}

/**
 * Updates the group
 */
function saveGroup()
{
    if ($('title').value.blank()) {
        alert(incomplete_fields);
        $('title').focus();
        return false;
    }

    if (selectedGroup == 0) {
        StaticPageAjax.callAsync(
            'insertgroup',
            $('title').value,
            $('fast_url').value,
            $('meta_keys').value,
            $('meta_desc').value,
            $('visible').value
        );
    } else {
        StaticPageAjax.callAsync(
            'updategroup',
            selectedGroup,
            $('title').value,
            $('fast_url').value,
            $('meta_keys').value,
            $('meta_desc').value,
            $('visible').value
        );
    }
}

/**
 * Deletes a group
 */
function deleteGroup(rowElement, gid)
{
    selectGridRow('groups_datagrid', rowElement.parentNode.parentNode);
    if (confirm(confirm_group_delete)) {
        StaticPageAjax.callAsync('deletegroup', gid);
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
    $('legend_title').set('html', add_group_title);
    $('title').value     = '';
    $('fast_url').value  = '';
    $('meta_keys').value = '';
    $('meta_desc').value = '';
    $('visible').value   = 'true';
    unselectGridRow('groups_datagrid');
    $('title').focus();
}

var StaticPageAjax = new JawsAjax('StaticPage', StaticPageCallback);

selectedGroup = 0;
var autoDraftDone = false;
