/**
 * BLOCKS JS Actions
 *
 * @category   Ajax
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Blocks callbacks
 */
var BlocksCallback = {

    NewBlock: function(response) {
        if (response['type'] == 'alert-success') {
            afterNewBlock(response['data']);
        }
    },

    UpdateBlock: function(response) {
        //
    },

    DeleteBlock: function(response) {
        // Remove item from block combo
        var combo = $('#block_id')[0];
        var auxStyle = combo.options[combo.selectedIndex].style.backgroundColor;
        selIndex = combo.selectedIndex;
        combo.options[selIndex] = null;
        // Set option styles...
        for (i = selIndex; i <= combo.length - 1; i++) {
            aux = combo.options[i].style.backgroundColor;
            combo.options[i].style.backgroundColor = auxStyle;
            auxStyle = aux;
        }
        // Select next
        if (combo.length > 0) {
            if (selIndex <= combo.length - 1) {
                combo.options[selIndex].selected = true;
            } else {
                selIndex = 0;
                combo.options[selIndex].selected = true;
            }
            if (response['type'] == 'alert-success') {
                edit(combo.options[selIndex].value);
            }
        } else {
            createNewBlock();
        }
    },

    ParseText: function(response) {
        $('#preview_contents').html(response);
    }
}

/**
 * Fill editor entries
 */
function fillEditorEntries(block_data)
{
    $('#block_id').prop('disabled', false);
    $('#hidden_id').val(block_data['id']);
    $('#block_title').val(block_data['title'].defilter());
    $('#block_summary').val(block_data['summary']);
    $('#block_content').val(block_data['content']);
    document.getElementsByName('display_title[]').item(0).checked = block_data['display_title'] == '1';
    currentMode = 'edit';
}

/**
 * Update a block
 */
function updateBlock()
{
    if (currentMode == 'new') {
        newBlock();
    } else {
        $('#block_id').prop('disabled', true);
        id      = $('#hidden_id').val();
        title   = $('#block_title').val();
        summary = $('#block_summary').val();
        content = $('#block_content').val();
        if (!title || !content)
        {
            alert(Jaws.gadgets.Blocks.defines.incompleteBlockFields);
            return false;
        }

        displayTitle = $('#display_title_true').prop('checked');
        // Call function
        loading_message = Jaws.gadgets.Blocks.defines.updatingMessage;
        BlocksAjax.callAsync('UpdateBlock', [id, title, summary, content, displayTitle]);
        // Update Combo
        var combo = $('#block_id')[0];
        combo.options[combo.selectedIndex].text = title;
        $('#block_id').prop('disabled', false);
    }
}

/**
 * Delete a block
 */
function deleteBlock()
{
    id = $('#hidden_id').val();
    $('#block_id').prop('disabled', true);
    loading_message = Jaws.gadgets.Blocks.defines.deletingMessage;
    BlocksAjax.callAsync('DeleteBlock', id);
}

/**
 * Switch to a given tab (edit or preview)
 */
function switchTab(c, title)
{
    if (title) {
        $('#editTab').html(title);
    } else {
        var editTitle = $('#editTab').html();
    }

    if (c == 'edit') {
        if (currentMode == 'new') {
            if (!Jaws.gadgets.Blocks.defines.aclAddBlock) {
                if ($('#saveButton').length) {
                    $('#saveButton').css('display', 'none');
                }
            }

            if (Jaws.gadgets.Blocks.defines.aclDeleteBlock) {
                $('#delButton').css('display', 'none');
            }

            $('#cancelButton').css('display', 'inline');
        } else {
            if (!Jaws.gadgets.Blocks.defines.aclEditBlock) {
                $('#saveButton').css('display', 'none');
            }

            if (Jaws.gadgets.Blocks.defines.aclDeleteBlock) {
                $('#delButton').css('display', 'inline');
            }

            $('#cancelButton').css('display', 'none');
        }
        $('#editTab').addClass("current");
        $('#previewTab').removeClass();
        $('#edit').css('display', 'block');
        $('#preview').css('display', 'none');
    } else if (c == 'preview') {
        $('#editTab').removeClass();
        $("#previewTab").addClass("current");
        $('#edit').css('display', 'none');
        $('#preview').css('display', 'block');
    }
}

/**
 * Switch to edit mode
 */
function edit(id)
{
    previousID  = id;
    currentMode = 'edit';
    $('#block_id').prop('disabled', false);
    loading_message = Jaws.gadgets.Blocks.defines.retrievingMessage;
    var block = BlocksAjax.callAsync('GetBlock', id, false, {'async': false});
    fillEditorEntries(block);
    $('#block_id_txt').html(id);
    switchTab('edit', block['title']);
}

/**
 * Switch to preview mode
 */
function preview()
{
    switchTab('preview');
    var block_content = $('#block_content').val();
    $('#preview_title').html($('#block_title').val());

    // Use this if you want to use plugins
    BlocksAjax.callAsync('ParseText', block_content);
    //$('#preview_contents').html(block_content);
}


/**
 * Switch to NEW mode
 */
function createNewBlock(title)
{
    currentMode = 'new';
    switchTab('edit', title);
    combo = $('#block_id')[0];
    combo.disabled = true;
    blockTitle = $('#block_title')[0];
    blockTitle.value = '';
    blockTitle.focus();
    $('#block_summary').val('');
    $('#block_content').val('');
}

/**
 * Insert new block
 */
function newBlock()
{
    summary = $('#block_summary').val();
    content = $('#block_content').val();
    if (!$('#block_title').val() || !content)
    {
        alert(Jaws.gadgets.Blocks.defines.incompleteBlockFields);
        return false;
    }

    displayTitle = $('#display_title_true').prop('checked');
    // Call function
    loading_message = Jaws.gadgets.Blocks.defines.savingMessage;
    BlocksAjax.callAsync('NewBlock', [$('#block_title').val(), summary, content, displayTitle]);
}

/**
 * Add to combo after insert a new block
 */
function afterNewBlock(id)
{
    combo = $('#block_id')[0];
    combo.disabled = false;
    combo.options[combo.length] = new Option($('#block_title').val(), id);
    combo.options[combo.length - 1].selected = true;
    edit(id);
}

/**
 * Return to edit mode
 */
function returnToEdit()
{
    combo      = $('#block_id');
    b          = $('#newButton');
    blockTitle = $('#block_title');

    if (combo.length > 0) {

        if (previousID == 'NEW') {
            blockTitle.value = '';
            blockTitle.focus();
            $('#block_summary').val('');
            $('#block_content').val('');
            b.disabled = true;
            combo.disabled = true;
        } else {
            loading_message = Jaws.gadgets.Blocks.defines.retrievingMessage;
            var block = BlocksAjax.callAsync('GetBlock', previousID, false, {'async': false});
            fillEditorEntries(block);
            b.disabled = false;
            combo.disabled = false;
        }
        currentMode = 'edit';
        switchTab('edit', editTitle);
    } else {
        currentMode = 'new';
        createNewBlock();
    }
}

/**
 * Get first block, if not exists then NEW.
 */
function getFirst()
{
    combo = $('#block_id')[0];
    if (combo.length > 0) {
        combo.options[0].selected = true;
        edit(combo.options[0].value);
    } else {
        createNewBlock();
    }
}

$(document).ready(function() {
    getFirst();
});

var BlocksAjax = new JawsAjax('Blocks', BlocksCallback),
    currentMode = 'edit',
    previousID  = 'NEW',
    editTitle   = '';
