/**
 * BLOCKS JS Actions
 *
 * @category   Ajax
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Blocks callbacks
 */
var BlocksCallback = {

    NewBlock: function(response) {
        if (response['type'] == 'response_notice') {
            afterNewBlock(response['data']);
        }
        showResponse(response);
    },

    UpdateBlock: function(response) {
        showResponse(response);
    },

    DeleteBlock: function(response) {
        // Remove item from block combo
        var combo = $('block_id');
        var auxStyle = combo.options[combo.selectedIndex].style.backgroundColor;
        selIndex = combo.selectedIndex;
        combo.options[selIndex].dispose();
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
            if (response[0]['type'] == 'response_notice') {
                edit(combo.options[selIndex].value);
            }
        } else {
            createNewBlock();
        }
        showResponse(response);
    },

    ParseText: function(response) {
        $('preview_contents').innerHTML = response;
    }
}

/**
 * Fill editor entries
 */
function fillEditorEntries(block_data)
{
    $('block_id').disabled    = false;
    $('hidden_id').value      = block_data['id'];
    $('block_title').value    = block_data['title'].defilter();
    changeEditorValue('block_contents', block_data['contents']);
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
        $('block_id').disabled = true;
        id       = $('hidden_id').value;
        title    = $('block_title').value;
        contents = getEditorValue('block_contents');
        if (title.blank() || contents.blank())
        {
            alert(incompleteBlockFields);
            return false;
        }

        displayTitle = document.getElementsByName('display_title[]').item(0).checked;
        // Call function
        loading_message = updatingMessage;
        BlocksAjax.callAsync('UpdateBlock', id, title, contents, displayTitle);
        // Update Combo
        var combo = $('block_id');
        combo.options[combo.selectedIndex].text = title;
        $('block_id').disabled    = false;
    }
}

/**
 * Delete a block
 */
function deleteBlock()
{
    id = $('hidden_id').value;
    $('block_id').disabled = true;
    loading_message = deletingMessage;
    BlocksAjax.callAsync('DeleteBlock', id);
}

/**
 * Switch to a given tab (edit or preview)
 */
function switchTab(c, title)
{
    var editDiv    = $('edit');
    var previewDiv = $('preview');
    var editTab    = $('editTab');
    if (title) {
        editTab.innerHTML = title;
    } else {
        var editTitle = editTab.innerHTML;
    }
    var previewTab    = $('previewTab');
    var previewButton = $('previewButton');
    var saveButton    = $('saveButton');
    var cancelButton  = $('cancelButton');
    var delButton     = $('delButton');

    if (c == 'edit') {
        if (currentMode == 'new') {
            if (aclAddBlock) {
                saveButton.onclick = function() {
                    newBlock();
                }
            } else {
                if (saveButton) {
                    saveButton.style.display = 'none';
                }
            }

            if (aclDeleteBlock) {
                delButton.style.display = 'none';
            }

            cancelButton.style.display = 'inline';
        } else {
            if (aclEditBlock) {
                saveButton.onclick = function() {
                    updateBlock();
                }
            } else {
                saveButton.style.display = 'none';
            }

            if (aclDeleteBlock) {
                delButton.style.display = 'inline';
            }

            cancelButton.style.display = 'none';
        }
        editTab.className        = 'current';
        previewTab.className     = '';
        editDiv.style.display    = 'block';
        previewDiv.style.display = 'none';
    } else if (c == 'preview') {
        editTab.className        = '';
        previewTab.className     = 'current';
        editDiv.style.display    = 'none';
        previewDiv.style.display = 'block';
    }
}

/**
 * Switch to edit mode
 */
function edit(id)
{
    previousID  = id;
    currentMode = 'edit';
    $('block_id').disabled = false;
    loading_message = retrievingMessage;
    var block = BlocksAjax.callSync('GetBlock', id);
    fillEditorEntries(block);
    $('block_id_txt').innerHTML = id;
    switchTab('edit', block['title']);
}

/**
 * Switch to preview mode
 */
function preview()
{
    switchTab('preview');
    var block_contents = getEditorValue('block_contents');
    $('preview_title').innerHTML = $('block_title').value;

    // Use this if you want to use plugins
    BlocksAjax.callAsync('ParseText', block_contents);
    //$('preview_contents').innerHTML = block_contents;
}


/**
 * Switch to NEW mode
 */
function createNewBlock(title)
{
    currentMode = 'new';
    switchTab('edit', title);
    combo = $('block_id');
    combo.disabled = true;
    blockTitle = $('block_title');
    blockTitle.value = '';
    blockTitle.focus();
    changeEditorValue('block_contents', '');
}

/**
 * Insert new block
 */
function newBlock()
{
    title = $('block_title').value;
    contents = getEditorValue('block_contents');
    if (title.blank() || contents.blank())
    {
        alert(incompleteBlockFields);
        return false;
    }

    displayTitle = document.getElementsByName('display_title[]').item(0).checked;
    // Call function
    loading_message = savingMessage;
    BlocksAjax.callAsync('NewBlock', title, contents, displayTitle);
}

/**
 * Add to combo after insert a new block
 */
function afterNewBlock(id)
{
    combo = $('block_id');
    combo.disabled = false;
    combo.options[combo.length] = new Option($('block_title').value, id);
    combo.options[combo.length - 1].selected = true;
    edit(id);
}

/**
 * Return to edit mode
 */
function returnToEdit()
{
    combo      = $('block_id');
    b          = $('newButton');
    blockTitle = $('block_title');

    if (combo.length > 0) {

        if (previousID == 'NEW') {
            blockTitle.value = '';
            blockTitle.focus();
            changeEditorValue('block_contents', '');
            b.disabled = true;
            combo.disabled = true;
        } else {
            loading_message = retrievingMessage;
            var block = BlocksAjax.callSync('GetBlock', previousID);
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
    combo = $('block_id');
    if (combo.length > 0) {
        combo.options[0].selected = true;
        edit(combo.options[0].value);
    } else {
        createNewBlock();
    }
}

var BlocksAjax = new JawsAjax('Blocks', BlocksCallback),
    currentMode = 'edit',
    previousID  = 'NEW',
    editTitle   = '';
