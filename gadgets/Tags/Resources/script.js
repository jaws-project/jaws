/**
 * Tags Javascript actions
 *
 * @category    Ajax
 * @package     Tags
 */
/**
 * Use async mode, create Callback
 */
var TagsCallback = {
    AddTag: function(response) {
        if (response['type'] == 'alert-success') {
            stopTagAction();
            $('#tags_datagrid')[0].addItem();
            getDG('tags_datagrid');
        }
    },

    UpdateTag: function(response) {
        if (response['type'] == 'alert-success') {
            stopTagAction();
            getDG('tags_datagrid');
        }
    },

    DeleteTags: function(response) {
        if (response['type'] == 'alert-success') {
            stopTagAction();
            getDG('tags_datagrid', $('#tags_datagrid')[0].getCurrentPage(), true);
        }
    },

    MergeTags: function(response) {
        if (response['type'] == 'alert-success') {
            stopTagAction();
            getDG('tags_datagrid', $('#tags_datagrid')[0].getCurrentPage(), true);
        }
    },

    SaveSettings: function(response) {
        //
    }
}

/**
 * Fetches tags data to fills the data grid
 */
function getTagsDataGrid(name, offset, reset)
{
    var tags = TagsAjax.callAsync(
        'SearchTags',
        {'gadgets_filter': $('#gadgets_filter').val(), 'name': $('#filter').val(), 'offset': offset},
        false, {'async': false}
    );

    if (reset) {
        $('#' + name)[0].setCurrentPage(0);
        stopTagAction();

        var total = TagsAjax.callAsync(
            'SizeOfTagsSearch',
            {'gadgets_filter': $('#gadgets_filter').val(), 'name': $('#filter').val()},
            false, {'async': false}
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
    $('#legend_title').html(Jaws.gadgets.Tags.defines.addTagTitle);

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
    var tag = TagsAjax.callAsync('GetTag', id, false, {'async': false});
    $('#id').val(id);
    $('#name').val(tag['name']);
    $('#title').val(tag['title']);
    $('#description').val(tag['description']);
    $('#meta_keywords').val(tag['meta_keywords']);
    $('#meta_description').val(tag['meta_description']);
    $('#btn_cancel').css('display', 'inline');
    $('#legend_title').html(Jaws.gadgets.Tags.defines.editTagTitle);
}

/**
 * Update a tag
 */
function updateTag()
{
    if (!$('#name').val()) {
        alert(Jaws.gadgets.Tags.defines.incompleteTagFields);
        return false;
    }

    if ($('#id').val() == 0) {
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
                'id': $('#id').val(),
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
    if (confirm(Jaws.gadgets.Tags.defines.confirmTagDelete)) {
        TagsAjax.callAsync('DeleteTags', new Array(id));
    }
    unselectGridRow('tags_datagrid');
}


/**
 * Executes an action on tags
 */
function tagsDGAction(combo)
{
    var rows = $('#tags_datagrid')[0].getSelectedRows();
    if (rows.length < 1) {
        return;
    }

    if (combo.val() == 'delete') {
        var confirmation = confirm(Jaws.gadgets.Tags.defines.confirmTagDelete);
        if (confirmation) {
            TagsAjax.callAsync('DeleteTags', rows);
        }
    } else if (combo.val() == 'merge') {
        if(rows.length<2) {
            alert(Jaws.gadgets.Tags.defines.selectMoreThanOneTags);
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
    var actions = TagsAjax.callAsync('GetGadgetActions', gadget, false, {'async': false});
    $('#actions').empty();

    var newoption = new Option("", "");
    $('#actions').add(newoption);
    for (var i=0;i<actions.length;i++)
    {
        var newoption = new Option(actions[i], actions[i]);
        $('#actions').add(newoption);
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
    TagsAjax.callAsync('SaveSettings', $('#tag_results_limit').val());
}

$(document).ready(function() {
    switch (Jaws.defines.mainAction) {
        case 'Tags':
            $('#gadgets_filter').selectedIndex = 0;
            initDataGrid('tags_datagrid', TagsAjax, getTagsDataGrid);
            break;

        case 'Properties':
            break;
    }
});

var TagsAjax = new JawsAjax('Tags', TagsCallback),
    selectedRow = null,
    selectedRowColor = null;
