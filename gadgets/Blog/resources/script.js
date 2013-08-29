/**
 * Blog Javascript actions
 *
 * @category   Ajax
 * @package    Blog
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var BlogCallback = {

    deleteentries: function(response) {
        if (response[0]['css'] == 'notice-message') {
            var rows = _('posts_datagrid').getSelectedRows();
            if (rows.length > 0) {
                for(var i=0; i<rows.length; i++) {
                    _('posts_datagrid').deleteItem();
                }
            }
            PiwiGrid.multiSelect(_('posts_datagrid'));
            var limit = _('posts_datagrid').getCurrentPage();
            var formData = getDataOfLEForm();
            updatePostsDatagrid(formData['period'], formData['category'],
                                formData['status'], formData['search'], 0, true);
        } else {
            PiwiGrid.multiSelect(_('posts_datagrid'));
        }
        showResponse(response);
    },

    changeentrystatus: function(response) {
        if (response[0]['css'] == 'notice-message') {
            PiwiGrid.multiSelect(_('posts_datagrid'));
            resetLEForm();
            var formData = getDataOfLEForm();
            updatePostsDatagrid(formData['period'], formData['category'],
                                formData['status'], formData['search'], 0, true);
        } else {
            PiwiGrid.multiSelect(_('posts_datagrid'));
        }
        showResponse(response);
    },

    deletetrackbacks: function(response) {
        if (response[0]['css'] == 'notice-message') {
            var rows = _('trackbacks_datagrid').getSelectedRows();
            if (rows.length > 0) {
                for(var i=0; i<rows.length; i++) {
                    _('trackbacks_datagrid').deleteItem();
                }
            }
            PiwiGrid.multiSelect(_('trackbacks_datagrid'));
            var limit = _('trackbacks_datagrid').getCurrentPage();
            var formData = getDataOfLTBForm();
            updateTrackbacksDatagrid(limit, formData['filter'],
                                   formData['search'], formData['status'],
                                   true);
        } else {
            PiwiGrid.multiSelect(_('trackbacks_datagrid'));
        }
        showResponse(response);
    },

    trackbackmarkas: function(response) {
        if (response[0]['css'] == 'notice-message') {
            PiwiGrid.multiSelect(_('trackbacks_datagrid'));
            resetLTBForm();
            var formData = getDataOfLTBForm();
            updateTrackbacksDatagrid(0,
                                   formData['filter'],
                                   formData['search'],
                                   formData['status'],
                                   true);
        } else {
            PiwiGrid.multiSelect(_('trackbacks_datagrid'));
        }
        showResponse(response);
    },

    savesettings: function(response) {
        showResponse(response);
    },

    getcategoryform: function(response) {
        fillCatInfoForm(response);
    },

    addcategory: function(response) {
        showResponse(response);
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            resetCategoryCombo();
        }
    },

    updatecategory: function(response) {
        showResponse(response);
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            resetCategoryCombo();
        }
    },

    deletecategory: function(response) {
        showResponse(response);
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            resetCategoryCombo();
        }
    },

    autodraft: function(response) {
        showSimpleResponse(response);
    }
}

/**
 * Reset ListEntries form
 */
function resetLEForm()
{
    var form = document.forms['ListEntries'];
    form.elements['show'].value     = '';
    form.elements['category'].value = '';
    form.elements['status'].value   = '';
    form.elements['search'].value   = '';
}

/**
 * Reset ListComments form
 */
function resetLCForm()
{
    var form = document.forms['ListComments'];
    form.elements['filterby'].value = '';
    form.elements['filter'].value   = '';
    form.elements['status'].value   = 'approved';
}

/**
 * Reset ListTrackbacks form
 */
function resetLTBForm()
{
    var form = document.forms['ListTrackbacks'];
    form.elements['filterby'].value = '';
    form.elements['filter'].value   = '';
    form.elements['status'].value   = 'various';
}

/**
 * Get data of the form ListEntries form
 */
function getDataOfLEForm()
{
    var form = document.forms['ListEntries'];

    var data = new Array();

    data['period']   = form.elements['show'].value;
    data['category'] = form.elements['category'].value;
    data['status']   = form.elements['status'].value;
    data['search']   = form.elements['search'].value;

    return data;
}

/**
 * Get data of the form ListComments form
 */
function getDataOfLCForm()
{
    var form = document.forms['ListComments'];

    var data = new Array();

    data['filter']   = form.elements['filterby'].value;
    data['search']   = form.elements['filter'].value;
    data['status']   = form.elements['status'].value;

    return data;
}

/**
 * Get data of the form ListTrackbacks form
 */
function getDataOfLTBForm()
{
    var form = document.forms['ListTrackbacks'];

    var data = new Array();

    data['filter']   = form.elements['filterby'].value;
    data['search']   = form.elements['filter'].value;
    data['status']   = form.elements['status'].value;

    return data;
}

/**
 * Prepare the preview
 */
function parseText(form)
{
    var title   = form.elements['title'].value;
    var content = getEditorValue('text_block');
    content = BlogAjax.callSync('parsetext', content);

    var preview = document.getElementById('preview');
    preview.style.display = 'block';

    var titlePreview   = document.getElementById('previewTitle');
    var contentPreview = document.getElementById('previewContent');

    titlePreview.innerHTML   = title;
    contentPreview.innerHTML = content;
}

/**
 * search for a post
 */
function searchPost()
{
    var formData = getDataOfLEForm();
    updatePostsDatagrid(formData['period'], formData['category'],
                        formData['status'], formData['search'], 0, true);

    return false;
}

/**
 * search for a trackback
 */
function searchTrackback()
{
    var formData = getDataOfLTBForm();
    updateTrackbacksDatagrid(0, formData['filter'], formData['search'], formData['status'], true);
    return false;
}

/**
 * Update post datagrid
 */
function updatePostsDatagrid(period, cat, status, search, limit, resetCounter)
{
    var result = BlogAjax.callSync('searchposts', period, cat, status, search, limit);
    resetGrid('posts_datagrid', result);
    if (resetCounter) {
        var size = BlogAjax.callSync('sizeofsearch', period, cat, status, search);
        _('posts_datagrid').rowsSize    = size;
        _('posts_datagrid').setCurrentPage(0);
        _('posts_datagrid').updatePageCounter();
    }
}

/**
 * Get posts data
 */
function getData(limit)
{
    switch(_('action').value) {
    case 'ListEntries':
        if (limit == undefined) {
            limit = _('posts_datagrid').getCurrentPage();
        }
        var formData = getDataOfLEForm();
        updatePostsDatagrid(formData['period'], formData['category'],
                            formData['status'], formData['search'],
                            limit, false);
        break;
    case 'ManageComments':
        if (limit == undefined) {
            limit = _('comments_datagrid').getCurrentPage();
        }
        var formData = getDataOfLCForm();
        updateCommentsDatagrid(limit, formData['search'], formData['status'], false);
        break;
    case 'ManageTrackbacks':
        if (limit == undefined) {
            limit = _('trackbacks_datagrid').getCurrentPage();
        }
        var formData = getDataOfLTBForm();
        updateTrackbacksDatagrid(limit, formData['filter'],
                                 formData['search'], formData['status'],
                                 false);
        break;
    }
}

/**
 * Get first values of posts or comments
 */
function firstValues()
{
    switch(_('action').value) {
    case 'ListEntries':
        var firstValues = _('posts_datagrid').getFirstPagerValues();
        getData(firstValues);
        _('posts_datagrid').firstPage();
        break;
    case 'ManageComments':
        var firstValues = _('comments_datagrid').getFirstPagerValues();
        getData(firstValues);
        _('comments_datagrid').firstPage();
        break;
    case 'ManageTrackbacks':
        var firstValues = _('trackbacks_datagrid').getFirstPagerValues();
        getData(firstValues);
        _('trackbacks_datagrid').firstPage();
        break;
    }
}

/**
 * Get previous values of posts or comments
 */
function previousValues()
{
    switch(_('action').value) {
    case 'ListEntries':
        var previousValues = _('posts_datagrid').getPreviousPagerValues();
        getData(previousValues);
        _('posts_datagrid').previousPage();
        break;
    case 'ManageComments':
        var previousValues = _('comments_datagrid').getPreviousPagerValues();
        getData(previousValues);
        _('comments_datagrid').previousPage();
        break;
    case 'ManageTrackbacks':
        var previousValues = _('trackbacks_datagrid').getPreviousPagerValues();
        getData(previousValues);
        _('trackbacks_datagrid').previousPage();
        break;
    }
}

/**
 * Get next values of posts or comments
 */
function nextValues()
{
    switch(_('action').value) {
    case 'ListEntries':
        var nextValues = _('posts_datagrid').getNextPagerValues();
        getData(nextValues);
        _('posts_datagrid').nextPage();
        break;
    case 'ManageComments':
        var nextValues = _('comments_datagrid').getNextPagerValues();
        getData(nextValues);
        _('comments_datagrid').nextPage();
        break;
    case 'ManageTrackbacks':
        var nextValues = _('trackbacks_datagrid').getNextPagerValues();
        getData(nextValues);
        _('trackbacks_datagrid').nextPage();
        break;
    }
}

/**
 * Get last values of posts or comments
 */
function lastValues()
{
    switch(_('action').value) {
    case 'ListEntries':
        var lastValues = _('posts_datagrid').getLastPagerValues();
        getData(lastValues);
        _('posts_datagrid').lastPage();
        break;
    case 'ManageComments':
        var lastValues = _('comments_datagrid').getLastPagerValues();
        getData(lastValues);
        _('comments_datagrid').lastPage();
        break;
    case 'ManageTrackbacks':
        var lastValues = _('trackbacks_datagrid').getLastPagerValues();
        getData(lastValues);
        _('trackbacks_datagrid').lastPage();
        break;
    }
}


/**
 * Update trackbacks datagrid
 */
function updateTrackbacksDatagrid(limit, filter, search, status, resetCounter)
{
    result = BlogAjax.callSync('searchtrackbacks', limit, filter, search, status);
    resetGrid('trackbacks_datagrid', result);
    if (resetCounter) {
        var size = BlogAjax.callSync('sizeoftrackbackssearch', filter, search, status);
        _('trackbacks_datagrid').rowsSize    = size;
        _('trackbacks_datagrid').setCurrentPage(0);
        _('trackbacks_datagrid').updatePageCounter();
    }
}


/**
 * Delete trackback
 */
function trackbackDelete(row_id)
{
    var confirmation = confirm(deleteConfirm);
    if (confirmation) {
        BlogAjax.callAsync('deletetrackbacks', row_id);
    }
}

/**
 * Executes an action on trackbacks
 */
function trackbackDGAction(combo)
{
    var rows = _('trackbacks_datagrid').getSelectedRows();
    var selectedRows = false;
    if (rows.length > 0) {
        selectedRows = true;
    }

     if (combo.value == 'delete') {
        if (selectedRows) {
            var confirmation = confirm(deleteConfirm);
            if (confirmation) {
                BlogAjax.callAsync('deletetrackbacks', rows);
            }
        }
    } else if (combo.value != '') {
        if (selectedRows) {
            BlogAjax.callAsync('trackbackmarkas', rows, combo.value);
        }
    }
}

/**
 * Executes an action on trackbacks
 */
function entryDGAction(combo)
{
    var rows = _('posts_datagrid').getSelectedRows();
    var selectedRows = false;
    if (rows.length > 0) {
        selectedRows = true;
    }

     if (combo.value == 'delete') {
        if (selectedRows) {
            var confirmation = confirm(deleteConfirm);
            if (confirmation) {
                BlogAjax.callAsync('deleteentries', rows);
            }
        }
    } else if (combo.value != '') {
        if (selectedRows) {
            BlogAjax.callAsync('changeentrystatus', rows, combo.value);
        }
    }
}

/**
 * Update the blog settings
 */
function saveSettings(form)
{
    var defaultView      = form.elements['default_view'].value;
    var lastEntries      = form.elements['last_entries_limit'].value;
    var popularLimit     = form.elements['popular_limit'].value;
    var lastComments     = form.elements['last_comments_limit'].value;
    var recentComments   = form.elements['last_recentcomments_limit'].value;
    var defaultCat       = form.elements['default_category'].value;
    var xmlLimit         = form.elements['xml_limit'].value;
    var comments         = form.elements['comments'].value;
    var comment_status   = form.elements['comment_status'].value;
    var trackback        = form.elements['trackback'].value;
    var trackback_status = form.elements['trackback_status'].value;
    var pingback         = form.elements['pingback'].value;

    BlogAjax.callAsync('savesettings', defaultView, lastEntries, popularLimit, lastComments, recentComments, defaultCat, 
                      xmlLimit, comments, comment_status, trackback, trackback_status,
                      pingback);
}

/**
 * Edit the category
 */
function editCategory(id)
{
    if (id == 0) return;
    selectedCategory = id;

    _('legend_title').innerHTML = editCategory_title;
    _('btn_delete').style.display = 'inline';
    var category = BlogAjax.callSync('getcategory', id);

    _('name').value             = category['name'];
    _('fast_url').value         = category['fast_url'];
    _('meta_keywords').value    = category['meta_keywords'];
    _('meta_desc').value        = category['meta_description'];
    _('description').value      = category['description'];
}

/**
 * Reset the category Info Form values
 */
function resetCategoryForm()
{
    BlogAjax.callAsync('getcategoryform', 'new', 0);
    _('category_id').selectedIndex = -1;
}

/**
 * Get the big combo
 */
function resetCategoryCombo()
{
    var categories = BlogAjax.callSync('getcategories');
    _('category_id').innerHTML = '';
    categories.each(function (item, key){
        var newoption = new Option(item['name'], item['id']);
        _('category_id').add(newoption);
        __('#category_id > option:even').addClass('piwi_option_odd');
        __('#category_id > option:odd').addClass('piwi_option_even');
    });
}

/**
 * Save the info of a category, updating or adding.
 */
function saveCategory(form)
{
    if (_('name').value.blank())
    {
        alert(incompleteCategoryFields);
        return false;
    }

    if (selectedCategory == null) {
        BlogAjax.callAsync('addcategory',
                            _('name').value,
                            _('description').value,
                            _('fast_url').value,
                            _('meta_keywords').value,
                            _('meta_desc').value);
    } else {
        BlogAjax.callAsync('updatecategory',
                            selectedCategory,
                            _('name').value,
                            _('description').value,
                            _('fast_url').value,
                            _('meta_keywords').value,
                            _('meta_desc').value);
    }
}

/**
 * Fill the Category Info Form
 */
function fillCatInfoForm(content)
{
    var catInfo = document.getElementById('catinfoform');
    catInfo.innerHTML = content;
    _('description').value = content.evalScripts().toString().defilter();
}

/**
 * Delete category
 */
function deleteCategory()
{
    if (confirm(deleteMessage)) {
        BlogAjax.callAsync('deletecategory', selectedCategory);
    }
}

/**
 * Create a new category
 */
function newCategory()
{
    resetCategoryForm();
}

/**
 * Just the mother function that will make sure that auto drafting is running
 * and is being run every ~ 120 seconds (2 minutes).
 *
 * @see AutoDraft();
 */
function startAutoDrafting()
{
    var title          = _('title').value;
    var fasturl        = _('fasturl').value;
    var meta_keywords  = _('meta_keywords').value;
    var meta_desc      = _('meta_desc').value;
    var allow_comments = _('allow_comments').checked;
    var trackbacks     = '';
    if (_('trackback_to')) {
        trackbacks = _('trackback_to').value;
    }
    var published      = _('published').value;
    var summary        = getEditorValue('summary_block');
    var content        = getEditorValue('text_block');

    if (!title.blank() && (!summary.blank() || !content.blank()))
    {
        var timestamp = null;
        if (_('edit_timestamp').checked) {
            timestamp = _('pubdate').value;
        }

        var categoriesNode = _('categories').getElementsByTagName('input');
        var categories     = new Array();
        var catCounter     = 0;
        for(var i = 0; i < categoriesNode.length; i++) {
            if (categoriesNode[i].checked) {
                categories[catCounter] = categoriesNode[i].value;
                catCounter++;
            }
        }

        var id = '';
        var actioni = _('action').value;

        switch (actioni) {
            case 'SaveNewEntry':
                id = 'NEW';
                break;
            case 'SaveEditEntry':
                id = _('id').value;
                break;
        }

        BlogAjax.callAsync('autodraft', id, categories, title, summary, content, fasturl, meta_keywords, meta_desc,
                       allow_comments, trackbacks, published, timestamp);
    }
    setTimeout('startAutoDrafting();', 120000);
}

/**
 * Auto Draft response
 */
function showSimpleResponse(message)
{
    if (!autoDraftDone) {
        var actioni   = _('action').value;
        if (actioni == 'SaveNewEntry' && message[0]['css'] == 'notice-message') {
            _('published').value = '0';
            _('id').value        = message[0]['message']['id'];
            _('action').value    = 'SaveEditEntry';
            message[0]['message'] = message[0]['message']['message'];
        }
        autoDraftDone = true;
    }
    showResponse(message, false);
}

/**
 * Toggle advanced
 */
function toggleAdvanced(checked)
{
    if (checked) {
        _('advanced').style.display = 'block';
    } else {
        _('advanced').style.display = 'none';
    }
}

/**
 * Toggle update publication time
 */
function toggleUpdate(checked)
{
    if (checked) {
        _('pubdate').disabled = false;
        _('pubdate_button').disabled = false;
    } else {
        _('pubdate').disabled = true;
        _('pubdate_button').disabled = true;
    }
}

/**
 * Stops doing a certain action
 */
function stopAction() {
    ('legend_title').innerHTML = addCategory_title;
    _('btn_delete').style.display = 'none';
    selectedCategory = null;

    _('category_id').selectedIndex  = -1;

    _('name').value             = '';
    _('fast_url').value         = '';
    _('meta_keywords').value    = '';
    _('meta_desc').value        = '';
    _('description').value      = '';
}

var BlogAjax = new JawsAjax('Blog', BlogCallback);

var firstFetch = true;
var autoDraftDone = false;

//current group
var selectedCategory = null;
