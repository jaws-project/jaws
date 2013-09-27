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
    UpdateTag: function(response) {
        if (response[0].type == 'response_notice') {
            stopTagAction();
            getDG('tags_datagrid');
        }
        showResponse(response);
    },

    DeleteTags: function(response) {
        if (response[0].type == 'response_notice') {
            stopTagAction();
            getDG('tags_datagrid');
        }
        showResponse(response);
    },

    MarkAs: function(response) {
        if (response[0].type == 'response_notice') {
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
        offset,
        $('gadgets_filter').value,
        $('filter').value,
        $('actions').value
    );
    if (reset) {
        stopTagAction();
        $(name).setCurrentPage(0);
        var total = TagsAjax.callSync(
            'SizeOfTagsSearch',
            $('gadgets_filter').value,
            $('filter').value,
            $('actions').value
        );
    }

    resetGrid(name, tags, total);
}

function isValidEmail(email) {
    return (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(email));
}

/**
 * Clean the form
 *
 */
function stopTagAction()
{
    $('id').value                      = 0;
    $('name').value                    = '';
    $('btn_save').style.display        = 'none';
    $('btn_cancel').style.display      = 'none';
    $('name').disabled                 = false;

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
    $('btn_save').style.display   = 'inline';
    $('btn_cancel').style.display = 'inline';
}

/**
 * Update a tag
 */
function updateTag() {
    TagsAjax.callAsync('UpdateTag', $('id').value, $('name').value);
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
 * Executes an action on comments
 */
function tagsDGAction(combo)
{
    var rows = $('tags_datagrid').getSelectedRows();
    var selectedRows = false;
    if (rows.length > 0) {
        selectedRows = true;
    }

     if (combo.value == 'delete') {
        if (selectedRows) {
            var confirmation = confirm(confirmCommentDelete);
            if (confirmation) {
                TagsAjax.callAsync('DeleteComments', rows);
            }
        }
    } else if (combo.value != '') {
        if (selectedRows) {
            TagsAjax.callAsync('MarkAs', $('gadget').value, rows, combo.value);
        }
    }
}

/**
 * change gadget combo (fetch available action list)
 */
function changeGadget(gadget)
{
    alert(gadget.value);
    var tag = TagsAjax.callSync('GetGadgetActions', gadget.value);
    $('id').value                 = id;
    $('name').value               = tag['name'];
    $('btn_save').style.display   = 'inline';
    $('btn_cancel').style.display = 'inline';
}

/**
 * search for a comment
 */
function searchTags()
{
    getTagsDataGrid('tags_datagrid', 0, true);
}

/**
 * save properties
 */
function SaveSettings()
{
    TagsAjax.callAsync('SaveSettings', $('allow_comments').value, $('allow_duplicate').value);
}

var TagsAjax = new JawsAjax('Tags', TagsCallback),
    selectedRow = null,
    selectedRowColor = null;
