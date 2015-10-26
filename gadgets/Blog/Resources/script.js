/**
 * Blog Javascript actions
 *
 * @category   Ajax
 * @package    Blog
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var BlogCallback = {

    DeleteEntries: function(response) {
        if (response[0]['type'] == 'response_notice') {
            var rows = $('posts_datagrid')[0].getSelectedRows();
            if (rows.length > 0) {
                for(var i=0; i<rows.length; i++) {
                    $('posts_datagrid')[0].deleteItem();
                }
            }
            PiwiGrid.multiSelect($('posts_datagrid'));
            var limit = $('posts_datagrid')[0].getCurrentPage();
            var formData = getDataOfLEForm();
            updatePostsDatagrid(formData['category'],
                                formData['status'], formData['search'], 0, true);
        } else {
            PiwiGrid.multiSelect($('posts_datagrid'));
        }
        BlogAjax.showResponse(response);
    },

    ChangeEntryStatus: function(response) {
        if (response[0]['type'] == 'response_notice') {
            PiwiGrid.multiSelect($('posts_datagrid'));
            resetLEForm();
            var formData = getDataOfLEForm();
            updatePostsDatagrid(formData['category'],
                                formData['status'], formData['search'], 0, true);
        } else {
            PiwiGrid.multiSelect($('posts_datagrid'));
        }
        BlogAjax.showResponse(response);
    },

    DeleteTrackbacks: function(response) {
        if (response[0]['type'] == 'response_notice') {
            var rows = $('trackbacks_datagrid')[0].getSelectedRows();
            if (rows.length > 0) {
                for(var i=0; i<rows.length; i++) {
                    $('trackbacks_datagrid')[0].deleteItem();
                }
            }
            PiwiGrid.multiSelect($('trackbacks_datagrid'));
            var limit = $('trackbacks_datagrid')[0].getCurrentPage();
            var formData = getDataOfLTBForm();
            updateTrackbacksDatagrid(limit, formData['filter'],
                                   formData['search'], formData['status'],
                                   true);
        } else {
            PiwiGrid.multiSelect($('trackbacks_datagrid'));
        }
        BlogAjax.showResponse(response);
    },

    TrackbackMarkAs: function(response) {
        if (response[0]['type'] == 'response_notice') {
            PiwiGrid.multiSelect($('trackbacks_datagrid'));
            resetLTBForm();
            var formData = getDataOfLTBForm();
            updateTrackbacksDatagrid(0,
                                   formData['filter'],
                                   formData['search'],
                                   formData['status'],
                                   true);
        } else {
            PiwiGrid.multiSelect($('trackbacks_datagrid'));
        }
        BlogAjax.showResponse(response);
    },

    SaveSettings: function(response) {
        BlogAjax.showResponse(response);
    },

    getcategoryform: function(response) {
        fillCatInfoForm(response);
    },

    AddCategory2: function(response) {
        BlogAjax.showResponse(response);
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            resetCategoryCombo();
        }
    },

    UpdateCategory2: function(response) {
        BlogAjax.showResponse(response);
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            resetCategoryCombo();
        }
    },

    DeleteCategory2: function(response) {
        BlogAjax.showResponse(response);
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            resetCategoryCombo();
        }
    },

    AutoDraft: function(response) {
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
    content = BlogAjax.callSync('ParseText', content);

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
    updatePostsDatagrid(formData['category'],
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
function updatePostsDatagrid(cat, status, search, limit, resetCounter)
{
    var result = BlogAjax.callSync('SearchPosts', [cat, status, search, limit]);
    resetGrid('posts_datagrid', result);
    if (resetCounter) {
        var size = BlogAjax.callSync('SizeOfSearch', [cat, status, search]);
        $('posts_datagrid')[0].rowsSize = size;
        $('posts_datagrid')[0].setCurrentPage(0);
        $('posts_datagrid')[0].updatePageCounter();
    }
}

/**
 * Get posts data
 */
function getData(limit)
{
    switch($('action').value) {
    case 'ListEntries':
        if (limit == undefined) {
            limit = $('posts_datagrid')[0].getCurrentPage();
        }
        var formData = getDataOfLEForm();
        updatePostsDatagrid(formData['category'],
                            formData['status'], formData['search'],
                            limit, false);
        break;
    case 'ManageComments':
        if (limit == undefined) {
            limit = $('comments_datagrid')[0].getCurrentPage();
        }
        var formData = getDataOfLCForm();
        updateCommentsDatagrid(limit, formData['search'], formData['status'], false);
        break;
    case 'ManageTrackbacks':
        if (limit == undefined) {
            limit = $('trackbacks_datagrid')[0].getCurrentPage();
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
    switch($('action').value) {
    case 'ListEntries':
        var firstValues = $('posts_datagrid')[0].getFirstPagerValues();
        getData(firstValues);
        $('posts_datagrid')[0].firstPage();
        break;
    case 'ManageComments':
        var firstValues = $('comments_datagrid')[0].getFirstPagerValues();
        getData(firstValues);
        $('comments_datagrid')[0].firstPage();
        break;
    case 'ManageTrackbacks':
        var firstValues = $('trackbacks_datagrid')[0].getFirstPagerValues();
        getData(firstValues);
        $('trackbacks_datagrid')[0].firstPage();
        break;
    }
}

/**
 * Get previous values of posts or comments
 */
function previousValues()
{
    switch($('action').value) {
    case 'ListEntries':
        var previousValues = $('posts_datagrid')[0].getPreviousPagerValues();
        getData(previousValues);
        $('posts_datagrid')[0].previousPage();
        break;
    case 'ManageComments':
        var previousValues = $('comments_datagrid')[0].getPreviousPagerValues();
        getData(previousValues);
        $('comments_datagrid')[0].previousPage();
        break;
    case 'ManageTrackbacks':
        var previousValues = $('trackbacks_datagrid')[0].getPreviousPagerValues();
        getData(previousValues);
        $('trackbacks_datagrid')[0].previousPage();
        break;
    }
}

/**
 * Get next values of posts or comments
 */
function nextValues()
{
    switch($('action').value) {
    case 'ListEntries':
        var nextValues = $('posts_datagrid')[0].getNextPagerValues();
        getData(nextValues);
        $('posts_datagrid')[0].nextPage();
        break;
    case 'ManageComments':
        var nextValues = $('comments_datagrid')[0].getNextPagerValues();
        getData(nextValues);
        $('comments_datagrid')[0].nextPage();
        break;
    case 'ManageTrackbacks':
        var nextValues = $('trackbacks_datagrid')[0].getNextPagerValues();
        getData(nextValues);
        $('trackbacks_datagrid')[0].nextPage();
        break;
    }
}

/**
 * Get last values of posts or comments
 */
function lastValues()
{
    switch($('action').value) {
    case 'ListEntries':
        var lastValues = $('posts_datagrid')[0].getLastPagerValues();
        getData(lastValues);
        $('posts_datagrid')[0].lastPage();
        break;
    case 'ManageComments':
        var lastValues = $('comments_datagrid')[0].getLastPagerValues();
        getData(lastValues);
        $('comments_datagrid')[0].lastPage();
        break;
    case 'ManageTrackbacks':
        var lastValues = $('trackbacks_datagrid')[0].getLastPagerValues();
        getData(lastValues);
        $('trackbacks_datagrid')[0].lastPage();
        break;
    }
}


/**
 * Update trackbacks datagrid
 */
function updateTrackbacksDatagrid(limit, filter, search, status, resetCounter)
{
    result = BlogAjax.callSync('SearchTrackbacks', [limit, filter, search, status]);
    resetGrid('trackbacks_datagrid', result);
    if (resetCounter) {
        var size = BlogAjax.callSync('SizeOfTrackbacksSearch', [filter, search, status]);
        $('trackbacks_datagrid')[0].rowsSize    = size;
        $('trackbacks_datagrid')[0].setCurrentPage(0);
        $('trackbacks_datagrid')[0].updatePageCounter();
    }
}


/**
 * Delete trackback
 */
function trackbackDelete(row_id)
{
    var confirmation = confirm(deleteConfirm);
    if (confirmation) {
        BlogAjax.callAsync('DeleteTrackbacks', row_id);
    }
}

/**
 * Executes an action on trackbacks
 */
function trackbackDGAction(combo)
{
    var rows = $('trackbacks_datagrid')[0].getSelectedRows();
    var selectedRows = false;
    if (rows.length > 0) {
        selectedRows = true;
    }

     if (combo.value == 'delete') {
        if (selectedRows) {
            var confirmation = confirm(deleteConfirm);
            if (confirmation) {
                BlogAjax.callAsync('DeleteTrackbacks', rows);
            }
        }
    } else if (combo.value != '') {
        if (selectedRows) {
            BlogAjax.callAsync('TrackbackMarkAs', [rows, combo.value]);
        }
    }
}

/**
 * Executes an action on trackbacks
 */
function entryDGAction(combo)
{
    var rows = $('posts_datagrid')[0].getSelectedRows();
    var selectedRows = false;
    if (rows.length > 0) {
        selectedRows = true;
    }

     if (combo.value == 'delete') {
        if (selectedRows) {
            var confirmation = confirm(deleteConfirm);
            if (confirmation) {
                BlogAjax.callAsync('DeleteEntries', rows);
            }
        }
    } else if (combo.value != '') {
        if (selectedRows) {
            BlogAjax.callAsync('ChangeEntryStatus', [rows, combo.value]);
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
    var trackback        = form.elements['trackback'].value;
    var trackback_status = form.elements['trackback_status'].value;
    var pingback         = form.elements['pingback'].value;

    BlogAjax.callAsync(
        'SaveSettings', [
            defaultView, lastEntries, popularLimit,
            lastComments, recentComments, defaultCat, 
            xmlLimit, comments, trackback, trackback_status, pingback
        ]
    );
}

/**
 * Edit the category
 */
function editCategory(id)
{
    if (id == 0) return;
    selectedCategory = id;

    $('#legend_title').html(editCategory_title);
    $('#btn_delete').css('display', 'inline');
    var category = BlogAjax.callSync('GetCategory', id);

    $('#name').val(category['name']);
    $('#fast_url').val(category['fast_url']);
    $('#meta_keywords').val(category['meta_keywords']);
    $('#meta_desc').val(category['meta_description']);
    $('#description').val(category['description']);
}

/**
 * Reset the category Info Form values
 */
function resetCategoryForm()
{
    BlogAjax.callAsync('getcategoryform', ['new', 0]);
    $('#category_id').prop('selectedIndex', -1);
}

/**
 * Get the big combo
 */
function resetCategoryCombo()
{
    var categories = BlogAjax.callSync('GetCategories');
    $('#category_id').html('');
    categories.each(function (item, key){
        var newoption = new Option(item['name'], item['id']);
        $('category_id').add(newoption);
        $('#category_id > option:even').addClass('piwi_option_odd');
        $('#category_id > option:odd').addClass('piwi_option_even');
    });
}

/**
 * Save the info of a category, updating or adding.
 */
function saveCategory(form)
{
    if (!$('name').val())
    {
        alert(incompleteCategoryFields);
        return false;
    }

    if (selectedCategory == null) {
        BlogAjax.callAsync(
            'AddCategory2', [
                $('#name').val(),
                $('#description').val(),
                $('#fast_url').val(),
                $('#meta_keywords').val(),
                $('#meta_desc').val()
            ]
        );
    } else {
        BlogAjax.callAsync(
            'UpdateCategory2', [
                selectedCategory,
                $('#name').val(),
                $('#description').val(),
                $('#fast_url').val(),
                $('#meta_keywords').val(),
                $('#meta_desc').val()
            ]
        );
    }
}

/**
 * Fill the Category Info Form
 */
function fillCatInfoForm(content)
{
    var catInfo = document.getElementById('catinfoform');
    catInfo.innerHTML = content;
    $('#description').val(content.evalScripts().toString().defilter());
}

/**
 * Delete category
 */
function deleteCategory()
{
    if (confirm(deleteMessage)) {
        BlogAjax.callAsync('DeleteCategory2', selectedCategory);
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
    var title          = $('title').value;
    var fasturl        = $('fasturl').value;
    var meta_keywords  = $('meta_keywords').value;
    var meta_desc      = $('meta_desc').value;
    var allow_comments = $('allow_comments').checked;
    var trackbacks     = '';
    if ($('trackback_to')) {
        trackbacks = $('trackback_to').value;
    }
    var published      = $('published').value;
    var summary        = getEditorValue('summary_block');
    var content        = getEditorValue('text_block');
    var tags           = '';
    if ($('tags') != null) {
        tags = $('tags').value;
    }

    if (!title.blank() && (!summary.blank() || !content.blank()))
    {
        var timestamp = null;
        if ($('edit_timestamp').checked) {
            timestamp = $('pubdate').value;
        }

        var categoriesNode = $('#categories input');
        var categories     = new Array();
        var catCounter     = 0;
        for(var i = 0; i < categoriesNode.length; i++) {
            if (categoriesNode[i].checked) {
                categories[catCounter] = categoriesNode[i].value;
                catCounter++;
            }
        }

        var id = '';
        var actioni = $('action').value;

        switch (actioni) {
            case 'SaveNewEntry':
                id = 'NEW';
                break;
            case 'SaveEditEntry':
                id = $('id').value;
                break;
        }

        BlogAjax.callAsync(
            'AutoDraft', [
                id, categories, title, summary,
                content, fasturl, meta_keywords, meta_desc,
                tags, allow_comments, trackbacks, published, timestamp
            ]
        );
    }
    setTimeout('startAutoDrafting();', 120000);
}

/**
 * Auto Draft response
 */
function showSimpleResponse(reponse)
{
    if (!autoDraftDone) {
        var actioni   = $('action').value;
        if (actioni == 'SaveNewEntry' && reponse[0]['type'] == 'response_notice') {
            $('#published').val('0');
            $('#id').val(reponse[0]['data']);
            $('#action').val('SaveEditEntry');
        }
        autoDraftDone = true;
    }
    BlogAjax.showResponse(reponse, false);
}

/**
 * Toggle advanced
 */
function toggleAdvanced(checked)
{
    if (checked) {
        $('#advanced').css('display', 'block');
    } else {
        $('#advanced').css('display', 'none');
    }
}

/**
 * Toggle update publication time
 */
function toggleUpdate(checked)
{
    if (checked) {
        $('#pubdate').prop('disabled', false);
        $('#pubdate_button').prop('disabled', false);
    } else {
        $('#pubdate').prop('disabled', true);
        $('#pubdate_button').prop('disabled', true);
    }
}

/**
 * Stops doing a certain action
 */
function stopAction() {
    ('#legend_title').html(addCategory_title);
    $('#btn_delete').css('display', 'none');
    selectedCategory = null;

    $('#category_id').prop('selectedIndex', -1);

    $('#name').val('');
    $('#fast_url').val('');
    $('#meta_keywords').val('');
    $('#meta_desc').val('');
    $('#description').val('');
}

var BlogAjax = new JawsAjax('Blog', BlogCallback);

var firstFetch = true;
var autoDraftDone = false;

//current group
var selectedCategory = null;
