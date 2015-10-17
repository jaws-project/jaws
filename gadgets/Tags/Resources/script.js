/**
 * Tags Javascript actions
 *
 * @category    Ajax
 * @package     Tags
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var TagsCallback = {
    AddTag: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopTagAction();
            $('tags_datagrid')[0].addItem();
            getDG('tags_datagrid');
        }
        TagsAjax.showResponse(response);
    },

    UpdateTag: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopTagAction();
            getDG('tags_datagrid');
        }
        TagsAjax.showResponse(response);
    },

    DeleteTags: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopTagAction();
            getDG('tags_datagrid', $('tags_datagrid')[0].getCurrentPage(), true);
        }
        TagsAjax.showResponse(response);
    },

    MergeTags: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopTagAction();
            getDG('tags_datagrid', $('tags_datagrid')[0].getCurrentPage(), true);
        }
        TagsAjax.showResponse(response);
    },

    SaveSettings: function(response) {
        TagsAjax.showResponse(response);
    }
}

/**
 * Fetches tags data to fills the data grid
 */
function getTagsDataGrid(name, offset, reset)
{
    var tags = TagsAjax.callSync(
        'SearchTags',
        {'gadget': $('#gadgets_filter').val(), 'name': $('#filter').val()},
        offset
    );

    if (reset) {
        $(name)[0].setCurrentPage(0);
        stopTagAction();

        var total = TagsAjax.callSync(
            'SizeOfTagsSearch',
            {'gadgets_filter': $('#gadgets_filter').val(), 'name': $('#filter').val()}
        );

    }

    resetGrid(name, tags, total);
}

/**
 * Clean the form
 *
 */
function stopTagAction()
{
    $('#id').val(0);
    $('#name').val('');
    $('#title').val('');
    $('#description').val('');
    $('#meta_keywords').val('');
    $('#meta_description').val('');
    $('#btn_cancel').css('display', 'none');
    $('#name').prop('disabled', false);
    $('#legend_title').html(addNewTagTitle);

    unselectGridRow('tags_datagrid');
    $('#name').focus();
}

/**
 * Edit a tag
 *
 */
function editTag(rowElement, id)
{
    selectGridRow('tags_datagrid', rowElement.parentNode.parentNode);
    var tag = TagsAjax.callSync('GetTag', id);
    $('#id').val(id);
    $('#name').val(tag['name']);
    $('#title').val(tag['title']);
    $('#description').val(tag['description']);
    $('#meta_keywords').val(tag['meta_keywords']);
    $('#meta_description').val(tag['meta_description']);
//    $('#btn_save').css('display', 'inline');
    $('#btn_cancel').css('display', 'inline');
    $('#legend_title').html(editTagTitle);
}

/**
 * Update a tag
 */
function updateTag()
{
    if (!$('name').val()) {
        alert(incompleteTafFields);
        return false;
    }

    if ($('id').value==0) {
        TagsAjax.callAsync(
            'AddTag', {
                'name':$('#name').val(),
                'title':$('#title').val(),
                'description':$('#description').val(),
                'meta_keywords':$('#meta_keywords').val(),
                'meta_description':$('#meta_description').val()
            }
        );
    } else {
        TagsAjax.callAsync(
            'UpdateTag', {
                0: $('#id').val(),
                'name':$('#name').val(),
                'title':$('#title').val(),
                'description':$('#description').val(),
                'meta_keywords':$('#meta_keywords').val(),
                'meta_description':$('#meta_description').val()
            }
        );
    }
}

/**
 * Delete a tag
 *
 */
function deleteTag(id)
{
    stopTagAction();
    if (confirm(confirmTagDelete)) {
        TagsAjax.callAsync('DeleteTags', new Array(id));
    }
    unselectGridRow('tags_datagrid');
}


/**
 * Executes an action on tags
 */
function tagsDGAction(combo)
{
    var rows = $('tags_datagrid')[0].getSelectedRows();
    if (rows.length < 1) {
        return;
    }

    if (combo.value == 'delete') {
        var confirmation = confirm(confirmTagDelete);
        if (confirmation) {
            TagsAjax.callAsync('DeleteTags', rows);
        }
    } else if (combo.value == 'merge') {
        if(rows.length<2) {
            alert(selectMoreThanOneTags);
            return;
        }
        var newName = prompt("Please enter new tag name:");
        if (newName.trim() == "") {
            return;
        }
        TagsAjax.callAsync('MergeTags', [rows, newName]);
    }
}

/**
 * change gadget combo (fetch available action list for gadget)
 */
function changeGadget(gadget)
{
    var actions = TagsAjax.callSync('GetGadgetActions', gadget);
    $('actions').empty();

    var newoption = new Option("", "");
    $('actions').add(newoption);
    for (var i=0;i<actions.length;i++)
    {
        var newoption = new Option(actions[i], actions[i]);
        $('actions').add(newoption);
    }
}

/**
 * search for a tag
 */
function searchTags()
{
    getTagsDataGrid('tags_datagrid', 0, true);
}

/**
 * save properties
 */
function saveSettings()
{
    TagsAjax.callAsync('SaveSettings', $('tag_results_limit').value);
}

var TagsAjax = new JawsAjax('Tags', TagsCallback),
    selectedRow = null,
    selectedRowColor = null;
