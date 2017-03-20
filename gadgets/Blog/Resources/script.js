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
        if (response[0]['type'] == 'alert-success') {
            var rows = $('#posts_datagrid')[0].getSelectedRows();
            if (rows.length > 0) {
                for(var i=0; i<rows.length; i++) {
                    $('#posts_datagrid')[0].deleteItem();
                }
            }
            PiwiGrid.multiSelect($('#posts_datagrid')[0]);
            var limit = $('#posts_datagrid')[0].getCurrentPage();
            var formData = getDataOfLEForm();
            updatePostsDatagrid(formData['category'],
                                formData['status'], formData['search'], 0, true);
        } else {
            PiwiGrid.multiSelect($('#posts_datagrid')[0]);
        }
        BlogAjax.showResponse(response);
    },

    ChangeEntryStatus: function(response) {
        if (response[0]['type'] == 'alert-success') {
            PiwiGrid.multiSelect($('#posts_datagrid')[0]);
            resetLEForm();
            var formData = getDataOfLEForm();
            updatePostsDatagrid(formData['category'],
                                formData['status'], formData['search'], 0, true);
        } else {
            PiwiGrid.multiSelect($('#posts_datagrid')[0]);
        }
        BlogAjax.showResponse(response);
    },

    DeleteTrackbacks: function(response) {
        if (response[0]['type'] == 'alert-success') {
            var rows = $('#trackbacks_datagrid')[0].getSelectedRows();
            if (rows.length > 0) {
                for(var i=0; i<rows.length; i++) {
                    $('#trackbacks_datagrid')[0].deleteItem();
                }
            }
            PiwiGrid.multiSelect($('#trackbacks_datagrid')[0]);
            var limit = $('#trackbacks_datagrid')[0].getCurrentPage();
            var formData = getDataOfLTBForm();
            updateTrackbacksDatagrid(limit, formData['filter'],
                                   formData['search'], formData['status'],
                                   true);
        } else {
            PiwiGrid.multiSelect($('#trackbacks_datagrid')[0]);
        }
        BlogAjax.showResponse(response);
    },

    TrackbackMarkAs: function(response) {
        if (response[0]['type'] == 'alert-success') {
            PiwiGrid.multiSelect($('#trackbacks_datagrid')[0]);
            resetLTBForm();
            var formData = getDataOfLTBForm();
            updateTrackbacksDatagrid(0,
                                   formData['filter'],
                                   formData['search'],
                                   formData['status'],
                                   true);
        } else {
            PiwiGrid.multiSelect($('#trackbacks_datagrid')[0]);
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
        if (response[0]['type'] == 'alert-success') {
            stopAction();
            resetCategoryCombo();
        }
    },

    UpdateCategory2: function(response) {
        BlogAjax.showResponse(response);
        if (response[0]['type'] == 'alert-success') {
            stopAction();
            resetCategoryCombo();
        }
    },

    DeleteCategory2: function(response) {
        BlogAjax.showResponse(response);
        if (response[0]['type'] == 'alert-success') {
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
    var content = $('#text_block').val();
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
        $('#posts_datagrid')[0].rowsSize = size;
        $('#posts_datagrid')[0].setCurrentPage(0);
        $('#posts_datagrid')[0].updatePageCounter();
    }
}

/**
 * Get posts data
 */
function getData(limit)
{
    switch($('#action').val()) {
    case 'ListEntries':
        if (limit == undefined) {
            limit = $('#posts_datagrid')[0].getCurrentPage();
        }
        var formData = getDataOfLEForm();
        updatePostsDatagrid(formData['category'],
                            formData['status'], formData['search'],
                            limit, false);
        break;
    case 'ManageComments':
        if (limit == undefined) {
            limit = $('#comments_datagrid')[0].getCurrentPage();
        }
        var formData = getDataOfLCForm();
        updateCommentsDatagrid(limit, formData['search'], formData['status'], false);
        break;
    case 'ManageTrackbacks':
        if (limit == undefined) {
            limit = $('#trackbacks_datagrid')[0].getCurrentPage();
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
    switch($('#action').val()) {
    case 'ListEntries':
        var firstValues = $('#posts_datagrid')[0].getFirstPagerValues();
        getData(firstValues);
        $('#posts_datagrid')[0].firstPage();
        break;
    case 'ManageComments':
        var firstValues = $('#comments_datagrid')[0].getFirstPagerValues();
        getData(firstValues);
        $('#comments_datagrid')[0].firstPage();
        break;
    case 'ManageTrackbacks':
        var firstValues = $('#trackbacks_datagrid')[0].getFirstPagerValues();
        getData(firstValues);
        $('#trackbacks_datagrid')[0].firstPage();
        break;
    }
}

/**
 * Get previous values of posts or comments
 */
function previousValues()
{
    switch($('#action').val()) {
    case 'ListEntries':
        var previousValues = $('#posts_datagrid')[0].getPreviousPagerValues();
        getData(previousValues);
        $('#posts_datagrid')[0].previousPage();
        break;
    case 'ManageComments':
        var previousValues = $('#comments_datagrid')[0].getPreviousPagerValues();
        getData(previousValues);
        $('#comments_datagrid')[0].previousPage();
        break;
    case 'ManageTrackbacks':
        var previousValues = $('#trackbacks_datagrid')[0].getPreviousPagerValues();
        getData(previousValues);
        $('#trackbacks_datagrid')[0].previousPage();
        break;
    }
}

/**
 * Get next values of posts or comments
 */
function nextValues()
{
    switch($('#action').val()) {
    case 'ListEntries':
        var nextValues = $('#posts_datagrid')[0].getNextPagerValues();
        getData(nextValues);
        $('#posts_datagrid')[0].nextPage();
        break;
    case 'ManageComments':
        var nextValues = $('#comments_datagrid')[0].getNextPagerValues();
        getData(nextValues);
        $('#comments_datagrid')[0].nextPage();
        break;
    case 'ManageTrackbacks':
        var nextValues = $('#trackbacks_datagrid')[0].getNextPagerValues();
        getData(nextValues);
        $('#trackbacks_datagrid')[0].nextPage();
        break;
    }
}

/**
 * Get last values of posts or comments
 */
function lastValues()
{
    switch($('#action').val()) {
    case 'ListEntries':
        var lastValues = $('#posts_datagrid')[0].getLastPagerValues();
        getData(lastValues);
        $('#posts_datagrid')[0].lastPage();
        break;
    case 'ManageComments':
        var lastValues = $('#comments_datagrid')[0].getLastPagerValues();
        getData(lastValues);
        $('#comments_datagrid')[0].lastPage();
        break;
    case 'ManageTrackbacks':
        var lastValues = $('#trackbacks_datagrid')[0].getLastPagerValues();
        getData(lastValues);
        $('#trackbacks_datagrid')[0].lastPage();
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
        $('#trackbacks_datagrid')[0].rowsSize    = size;
        $('#trackbacks_datagrid')[0].setCurrentPage(0);
        $('#trackbacks_datagrid')[0].updatePageCounter();
    }
}


/**
 * Delete trackback
 */
function trackbackDelete(row_id)
{
    var confirmation = confirm(jaws.Blog.Defines.deleteConfirm);
    if (confirmation) {
        BlogAjax.callAsync('DeleteTrackbacks', row_id);
    }
}

/**
 * Executes an action on trackbacks
 */
function trackbackDGAction(combo)
{
    var rows = $('#trackbacks_datagrid')[0].getSelectedRows();
    var selectedRows = false;
    if (rows.length > 0) {
        selectedRows = true;
    }

     if (combo.value == 'delete') {
        if (selectedRows) {
            var confirmation = confirm(jaws.Blog.Defines.deleteConfirm);
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
    var rows = $('#posts_datagrid')[0].getSelectedRows();
    var selectedRows = false;
    if (rows.length > 0) {
        selectedRows = true;
    }

     if (combo.value == 'delete') {
        if (selectedRows) {
            var confirmation = confirm(jaws.Blog.Defines.deleteConfirm);
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

    $('#legend_title').html(jaws.Blog.Defines.editCategory_title);
    $('#btn_delete').css('display', 'inline');
    var category = BlogAjax.callSync('GetCategory', id);

    $('#name').val(category['name']);
    $('#fast_url').val(category['fast_url']);
    $('#meta_keywords').val(category['meta_keywords']);
    $('#meta_desc').val(category['meta_description']);
    $('#description').val(category['description']);
    $('#image_preview').prop('src', category['image_url']);
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

    $.each(categories, function(key, value) {
        $('#category_id')
            .append($("<option></option>")
                .attr("value",value['id'])
                .text(value['name']));
    });

    $('#category_id > option:even').addClass('piwi_option_odd');
    $('#category_id > option:odd').addClass('piwi_option_even');
}

/**
 * Save the info of a category, updating or adding.
 */
function saveCategory()
{
    if (!$('#name').val())
    {
        alert(jaws.Blog.Defines.incompleteCategoryFields);
        return false;
    }

    if (selectedCategory == null) {
        BlogAjax.callAsync(
            'AddCategory2', {
                'name': $('#name').val(),
                'description': $('#description').val(),
                'fast_url': $('#fast_url').val(),
                'meta_keywords': $('#meta_keywords').val(),
                'meta_desc': $('#meta_desc').val(),
                'image': categoryImageInfo,
                'delete_image': deleteCategoryImage
            }
        );
    } else {
        BlogAjax.callAsync(
            'UpdateCategory2', {
                'id': selectedCategory,
                'name': $('#name').val(),
                'description': $('#description').val(),
                'fast_url': $('#fast_url').val(),
                'meta_keywords': $('#meta_keywords').val(),
                'meta_desc': $('#meta_desc').val(),
                'image': categoryImageInfo,
                'delete_image': deleteCategoryImage
            }
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
    if (confirm(jaws.Blog.Defines.deleteMessage)) {
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
    var title          = $('#title').val();
    var fasturl        = $('#fasturl').val();
    var meta_keywords  = $('#meta_keywords').val();
    var meta_desc      = $('#meta_desc').val();
    var allow_comments = $('#allow_comments').prop('checked');
    var trackbacks     = '';
    if ($('#trackback_to').length) {
        trackbacks = $('#trackback_to').val();
    }
    var published      = $('#published').val();
    var summary        = $('#summary_block').val();
    var content        = $('#text_block').val();
    var tags           = '';
    if ($('#tags').length) {
        tags = $('#tags').val();
    }

    if (!title.blank() && (!summary.blank() || !content.blank()))
    {
        var timestamp = null;
        if ($('#edit_timestamp').prop('checked')) {
            timestamp = $('#pubdate').val();
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
        var actioni = $('#action').val();

        switch (actioni) {
            case 'SaveNewEntry':
                id = 'NEW';
                break;
            case 'SaveEditEntry':
                id = $('#id').val();
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
        var actioni   = $('#action').val();
        if (actioni == 'SaveNewEntry' && reponse[0]['type'] == 'alert-success') {
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

function updateEditorsText(entryForm) {
    if (entryForm.elements['title'].value == '') {
        alert('{{missing_title}}');
        entryForm.elements['title'].focus();
        return false;
    }

    $('#summary_block').val($('#summary_block').val());
    $('#text_block').val($('#text_block').val());
    return true;
}

/**
 * Removes the image
 */
function removeImage() {
    $('#image_file').val('');
    $('#deleteImage').val('true');
    $('#blog_image').prop('src', 'gadgets/Blog/Resources/images/no-image.gif?' + (new Date()).getTime());
}

function previewImage(fileElement) {
    $('deleteImage').value = 'false';
    var fReader = new FileReader();
    fReader.readAsDataURL(fileElement.files[0]);
    fReader.onload = function (event) {
        document.getElementById('blog_image').src = event.target.result;
    }
}

/**
 * Uploads a single file using Ajax
 */
function uploadCategoryImage(fileElem) {
    var file = $('#image_file').get(0).files[0];

    var uploadCanceled = false,
        $col1 = $('<td>'),
        $col2 = $('<td>'),
        $row = $('<tr>').append($col1, $col2),
        $icon = $('<i>', {'class': 'fa fa-paperclip'}),
        $progressBar = $('<div>', {'class': 'progress-bar'}).css('width', '0'),
        $progress = $('<div>', {'class': 'progress'}).append($progressBar),
        $cancel = $('<i>', {'class': 'fa fa-times', title: 'LABELS.abort'});
    $cancel.click(function () {
        uploadCanceled = true;
    });
    $col2.append($progress, ' ', $cancel);
    $('#category_image').append($row);

    var xhr = BlogAjax.uploadFile('UploadImage', file,
        function (response, code) {
            if (code != 200) {
                if (code == 0) {
                    // abort
                }
                return;
            }
            if (response.type !== 'alert-success') {
                BlogAjax.showResponse(response);
                return;
            }
            categoryImageInfo = response.data;
            previewCategoryImage(fileElem);
            deleteCategoryImage = false;
            $progress.remove();
            $cancel.remove();
        },
        function (e) {
            if (uploadCanceled) {
                xhr.abort();
                $row.remove();
                uploadCanceled = false;
                return;
            }
            var percentage = Math.round((e.loaded * 100) / e.total) + '%';
            $progressBar.html(percentage).css('width', percentage);
        }
    );
}

/**
 * Removes the category image
 */
function removeCategoryImage() {
    $('#image_file').val('');
    deleteCategoryImage = true;
    $('#image_preview').prop('src', jaws.Blog.Defines.noImageURL + '?' + (new Date()).getTime());
}

function previewCategoryImage(fileElement) {
    $('deleteImage').value = 'false';
    var fReader = new FileReader();
    fReader.readAsDataURL(fileElement.files[0]);
    fReader.onload = function (event) {
        document.getElementById('image_preview').src = event.target.result;
    }
}

/**
 * Stops doing a certain action
 */
function stopAction() {
    $('#legend_title').html(jaws.Blog.Defines.addCategory_title);
    $('#btn_delete').css('display', 'none');
    selectedCategory = null;
    categoryImageInfo = null;
    deleteCategoryImage = false;

    $('#category_id').prop('selectedIndex', -1);

    $('#name').val('');
    $('#fast_url').val('');
    $('#meta_keywords').val('');
    $('#meta_desc').val('');
    $('#description').val('');
    $('#image_preview').prop('src', jaws.Blog.Defines.noImageURL);
}

$(document).ready(function() {
    switch (jaws.Defines.mainAction) {
        case 'EditEntry':
        case 'NewEntry':
            toggleUpdate(false);
            initDatePicker('pubdate');
            setTimeout('startAutoDrafting();', 120000);
            break;

        case 'ListEntries':
            getData();
            break;

        case 'ManageTrackbacks':
            getData();
            break;

        case 'ManageCategories':
            $('#legend_title').html(jaws.Blog.Defines.addCategory_title);
            break;
    }
});

var BlogAjax = new JawsAjax('Blog', BlogCallback);

var firstFetch = true;
var autoDraftDone = false;

//current group
var selectedCategory = null;

var categoryImageInfo = null;
var deleteCategoryImage = false;
