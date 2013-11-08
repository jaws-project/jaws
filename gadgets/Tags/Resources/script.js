/**
 * Tags Javascript actions
 *
 * @category    Ajax
 * @package     Tags
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var TagsCallback = {
    AddTag: function(response) {
        if (response[0].['type'] == 'response_notice') {
            stopTagAction();
            getDG('tags_datagrid');
        }
        showResponse(response);
    },

    UpdateTag: function(response) {
        if (response[0].['type'] == 'response_notice') {
            stopTagAction();
            getDG('tags_datagrid');
        }
        showResponse(response);
    },

    DeleteTags: function(response) {
        if (response[0].['type'] == 'response_notice') {
            stopTagAction();
            getDG('tags_datagrid');
        }
        showResponse(response);
    },

    MergeTags: function(response) {
        if (response[0].['type'] == 'response_notice') {
            stopTagAction();
            getDG('tags_datagrid');
        }
        showResponse(response);
    },

    SaveSettings: function(response) {
        showResponse(response);
    }
}

/**
 * Fetches tags data to fills the data grid
 */
function getTagsDataGrid(name, offset, reset)
{
    var tags = TagsAjax.callSync(
        'SearchTags',
        { 'gadget': $('gadgets_filter').value, 'name': $('filter').value},
        offset
    );

    if (reset) {
        $(name).setCurrentPage(0);
        stopTagAction();

        var total = TagsAjax.callSync(
            'SizeOfTagsSearch',
            { 'gadgets_filter': $('gadgets_filter').value, 'name': $('filter').value}
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
    $('id').value                      = 0;
    $('name').value                    = '';
    $('title').value                   = '';
    $('description').value             = '';
    $('meta_keywords').value           = '';
    $('meta_description').value        = '';
    $('btn_cancel').style.display      = 'none';
    $('name').disabled                 = false;
    $('legend_title').innerHTML        = addNewTagTitle;

    unselectGridRow('tags_datagrid');
    $('name').focus();
}

/**
 * Edit a tag
 *
 */
function editTag(rowElement, id)
{
    selectGridRow('tags_datagrid', rowElement.parentNode.parentNode);
    var tag = TagsAjax.callSync('GetTag', id);
    $('id').value                 = id;
    $('name').value               = tag['name'];
    $('title').value              = tag['title'];
    $('description').value        = tag['description'];
    $('meta_keywords').value      = tag['meta_keywords'];
    $('meta_description').value   = tag['meta_description'];
//    $('btn_save').style.display   = 'inline';
    $('btn_cancel').style.display = 'inline';
    $('legend_title').innerHTML   = editTagTitle;
}

/**
 * Update a tag
 */
function updateTag() {

    if($('id').value==0) {
        TagsAjax.callAsync('AddTag',
            {'name':$('name').value,
            'title':$('title').value,
            'description':$('description').value,
            'meta_keywords':$('meta_keywords').value,
            'meta_description':$('meta_description').value
            }
        );
    } else {
        TagsAjax.callAsync('UpdateTag',
            $('id').value,
            {'name':$('name').value,
             'title':$('title').value,
             'description':$('description').value,
             'meta_keywords':$('meta_keywords').value,
             'meta_description':$('meta_description').value
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
    var rows = $('tags_datagrid').getSelectedRows();
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
        TagsAjax.callAsync('MergeTags', rows, newName);
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
